<?php
namespace Alex;

class JsonResponse {
    private $code;
    private $body;

    function __construct($code = 200, $body = '') {
        $this->code = $code;
        $this->body = $body;
    }

    public function __toString() {
        header('content-type: application/json', true, $this->code);
        return json_encode($this->body);
    }
}