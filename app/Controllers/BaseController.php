
<?php
class BaseController {
    protected $request;
    protected $response;
    protected $view;
    protected $auth;

    public function __construct() {
        $app = Application::getInstance();
        $this->request = new Request();
        $this->response = new Response();
        $this->view = new View();
        $this->auth = $app->get('auth');
        
        // Condividi dati globali con tutte le viste
        if ($this->view && method_exists($this->view, 'share')) {
            $this->view->share('auth', $this->auth);
            $this->view->share('user', $this->auth ? $this->auth->user() : null);
            $this->view->share('app_name', $app->getConfig('app.name'));
        }
    }

    protected function view($template, $data = []) {
        if ($this->view && method_exists($this->view, 'render')) {
            echo $this->view->render($template, $data);
        } else {
            // Fallback semplice
            extract($data);
            $viewPath = "resources/views/" . str_replace('.', '/', $template) . '.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<h1>Vista non trovata: $template</h1>";
            }
        }
    }

    protected function json($data, $statusCode = 200) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($statusCode);
        }
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        $app = Application::getInstance();
        $fullUrl = $app->getFullUrl($url);
        
        if (!headers_sent()) {
            header('Location: ' . $fullUrl);
            exit;
        } else {
            echo "<script>window.location.href='" . htmlspecialchars($fullUrl) . "';</script>";
            echo "<meta http-equiv='refresh' content='0;url=" . htmlspecialchars($fullUrl) . "'>";
            exit;
        }
    }

    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            exit;
        }
    }

    protected function isAuthenticated() {
        if ($this->auth && method_exists($this->auth, 'check')) {
            return $this->auth->check();
        }
        
        // Fallback: verifica sessione
        return isset($_SESSION['user_id']);
    }

    protected function requirePermission($permission) {
        $this->requireAuth();
        
        if ($this->auth && method_exists($this->auth, 'hasPermission')) {
            if (!$this->auth->hasPermission($permission)) {
                http_response_code(403);
                echo "<h1>403 - Accesso Negato</h1>";
                echo "<p>Non hai i permessi necessari per accedere a questa risorsa.</p>";
                echo "<p><a href='/dashboard'>Torna alla Dashboard</a></p>";
                exit;
            }
        }
        // Se non c'è sistema di permessi, passa sempre (dev mode)
    }

    protected function validate($rules, $messages = []) {
        // Validazione semplice - da implementare se necessario
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $_POST[$field] ?? '';
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field][] = "Il campo $field è obbligatorio";
            }
            
            if (strpos($rule, 'email') !== false && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field][] = "Il campo $field deve essere un email valido";
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->back();
            exit;
        }
        
        return $_POST;
    }
}