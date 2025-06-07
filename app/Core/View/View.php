<?php
// app/Core/View/View.php
class View {
    private $viewsPath;
    private $data = [];

    public function __construct($viewsPath = 'resources/views') {
        $this->viewsPath = $viewsPath;
    }

    public function render($view, $data = []) {
        $this->data = array_merge($this->data, $data);
        
        $viewFile = $this->viewsPath . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View {$view} not found");
        }

        // Estrai le variabili per il template
        extract($this->data);
        
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        return $content;
    }

    public function share($key, $value) {
        $this->data[$key] = $value;
    }
}