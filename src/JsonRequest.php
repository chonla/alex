<?php
namespace Alex;

class JsonRequest {
    private $json;
    private $json_string;

    function __construct() {
        $this->json_string = file_get_contents('php://input');
        $this->json = json_decode($this->json_string, true);
    }

    public function toJson() {
        return $this->json;
    }

    public function __toString() {
        return $this->json_string;
    }
}