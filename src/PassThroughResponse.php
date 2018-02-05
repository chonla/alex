<?php
namespace Alex;

class PassThroughResponse {
    private $headers = [];
    private $body = '';

    function __construct($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $output = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $body = substr($output, $header_size);
        curl_close($ch);

        $this->headers = $this->parse_headers($headers);
        $this->body = $body;
    }

    private function parse_headers($headers_content) {
        $headers = [];
        $lines = explode("\r\n\r\n", $headers_content);
        $line_count = count($lines) - 1;
        for ($header_index = 0; $header_index < $line_count; $header_index++) {
            foreach(explode("\r\n", $lines[$header_index]) as $i => $line) {
                if ($i === 0) {
                    preg_match('@HTTP/(?<version>\d+\.\d+) (?<code>\d+) (?<message>.+)@', $line, $matches);
                    $headers[$header_index]['HTTP_VERSION'] = $matches['version'];
                    $headers[$header_index]['HTTP_CODE'] = (int)($matches['code']);
                    $headers[$header_index]['HTTP_MESSAGE'] = $matches['message'];
                    $headers[$header_index]['HTTP_HEADERS'] = [];
                } else {
                    list ($key, $value) = explode(': ', $line);
                    $headers[$header_index]['HTTP_HEADERS'][$key] = $value;
                }
            }
        }
        return $headers;
    }

    public function __toString() {

        foreach ($this->headers[count($this->headers) - 1]['HTTP_HEADERS'] as $header_key => $header_value) {
            if ($header_key === 'Transfer-Encoding') {
                $header_value = 'deflate';
            }
            header(sprintf('%s: %s', $header_key, $header_value));
        }
        header('X-Alex-PassThrough: yes', false, $this->headers[count($this->headers) - 1]['HTTP_CODE']);
        return $this->body;
    }
}