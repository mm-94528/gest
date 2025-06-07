<?php
// app/Core/Http/Request.php
class Request {
    private $data;

    public function __construct() {
        $this->data = array_merge($_GET, $_POST);
    }

    public function get($key = null, $default = null) {
        if ($key === null) {
            return $this->data;
        }
        
        return $this->data[$key] ?? $default;
    }

    public function has($key) {
        return isset($this->data[$key]);
    }

    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isGet() {
        return $this->method() === 'GET';
    }

    public function isPost() {
        return $this->method() === 'POST';
    }

    public function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function file($key) {
        return $_FILES[$key] ?? null;
    }

    public function validate($rules) {
        $validator = new Validator($this->data, $rules);
        return $validator->validate();
    }
}