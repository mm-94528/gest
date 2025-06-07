<?php
class Response {
    private $content;
    private $statusCode = 200;
    private $headers = [];

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode($code) {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json($data) {
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        return $this;
    }

    public function redirect($url) {
        $this->setStatusCode(302);
        $this->setHeader('Location', $url);
        return $this;
    }

    public function send() {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        echo $this->content;
    }
}