<?php
class Router {
    private $routes = [];
    private $moduleRoutes = [];
    private $currentRoute = null;
    private $debugMode = false;
    private $debugOutput = '';
    private $basePath = '';

    public function __construct($basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function addRoute($method, $uri, $handler) {
        $this->routes[$method][$uri] = $handler;
        return $this;
    }

    public function get($uri, $handler) {
        return $this->addRoute('GET', $uri, $handler);
    }

    public function post($uri, $handler) {
        return $this->addRoute('POST', $uri, $handler);
    }

    public function put($uri, $handler) {
        return $this->addRoute('PUT', $uri, $handler);
    }

    public function delete($uri, $handler) {
        return $this->addRoute('DELETE', $uri, $handler);
    }

    public function addModuleRoutes($module, $routes) {
        if (!is_array($routes)) {
            error_log("Warning: Routes for module '$module' is not an array, got: " . gettype($routes));
            return;
        }

        foreach ($routes as $route => $handler) {
            if (strpos($route, ' ') !== false) {
                list($method, $uri) = explode(' ', $route, 2);
                $this->moduleRoutes[$method][$uri] = [
                    'handler' => $handler,
                    'module' => $module
                ];
            }
        }
    }

    public function dispatch() {
        $uri = $this->getCurrentUri();
        $method = $this->getRequestMethod();

        $this->debugMode = isset($_GET['debug']);

        if ($this->debugMode) {
            $this->addDebugInfo("🔍 Debug Router Attivato");
            $this->addDebugInfo("URI completa: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
            $this->addDebugInfo("Base Path: '{$this->basePath}'");
            $this->addDebugInfo("URI processata: " . htmlspecialchars($uri));
            $this->addDebugInfo("Metodo: " . htmlspecialchars($method));
            $this->debugRoutes();
        }

        if ($this->matchModuleRoute($uri, $method)) {
            return;
        }

        if ($this->matchRoute($uri, $method)) {
            return;
        }

        $this->handleNotFound($uri, $method);
    }

    private function getCurrentUri() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Rimuovi query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Rimuovi il base path dall'URI
        if (!empty($this->basePath) && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        // Assicurati che inizi con /
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        // Rimuovi trailing slash (tranne per root)
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        return $uri;
    }

    private function getRequestMethod() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        return $method;
    }

    private function matchModuleRoute($uri, $method) {
        if (!isset($this->moduleRoutes[$method])) {
            if ($this->debugMode) {
                $this->addDebugInfo("❌ Nessuna route modulo per metodo: $method");
            }
            return false;
        }

        foreach ($this->moduleRoutes[$method] as $pattern => $routeData) {
            $params = $this->matchPattern($pattern, $uri);
            if ($params !== false) {
                if ($this->debugMode) {
                    $this->addDebugInfo("✅ Match route modulo: $method $pattern");
                }
                $this->executeHandler($routeData['handler'], $params, $routeData['module']);
                return true;
            }
        }

        if ($this->debugMode) {
            $this->addDebugInfo("❌ Nessun match nelle route moduli");
        }
        return false;
    }

    private function matchRoute($uri, $method) {
        if (!isset($this->routes[$method])) {
            if ($this->debugMode) {
                $this->addDebugInfo("❌ Nessuna route principale per metodo: $method");
            }
            return false;
        }

        foreach ($this->routes[$method] as $pattern => $handler) {
            if ($this->debugMode) {
                $this->addDebugInfo("  Confronto: '$pattern' con '$uri'");
            }

            $params = $this->matchPattern($pattern, $uri);
            if ($params !== false) {
                if ($this->debugMode) {
                    $this->addDebugInfo("✅ Match route principale: $method $pattern");
                }
                $this->executeHandler($handler, $params);
                return true;
            }
        }

        if ($this->debugMode) {
            $this->addDebugInfo("❌ Nessun match nelle route principali");
        }
        return false;
    }

    private function matchPattern($pattern, $uri) {
        // Match esatto prima
        if ($pattern === $uri) {
            return [];
        }
        
        // Converti pattern in regex per parametri dinamici
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $pattern . '$#';

        if (preg_match($regex, $uri, $matches)) {
            array_shift($matches);
            return $matches;
        }

        return false;
    }

    private function executeHandler($handler, $params = [], $module = null) {
        try {
            if ($this->debugMode) {
                $this->addDebugInfo("🚀 Esecuzione handler...");
            }

            if (is_string($handler)) {
                if (strpos($handler, '@') !== false) {
                    list($controllerName, $methodName) = explode('@', $handler);
                    
                    if ($module) {
                        $controllerClass = ucfirst($module) . '\\Controllers\\' . $controllerName;
                        if (!class_exists($controllerClass)) {
                            $controllerClass = $controllerName;
                        }
                    } else {
                        $controllerClass = $controllerName;
                    }

                    if (!class_exists($controllerClass)) {
                        throw new Exception("Controller {$controllerClass} not found");
                    }

                    $controller = new $controllerClass();
                    
                    if (!method_exists($controller, $methodName)) {
                        throw new Exception("Method {$methodName} not found in {$controllerClass}");
                    }

                    if ($this->debugMode && !headers_sent()) {
                        $this->printDebug();
                    }

                    call_user_func_array([$controller, $methodName], $params);
                }
            } elseif (is_callable($handler)) {
                if ($this->debugMode && !headers_sent()) {
                    $this->printDebug();
                }
                
                call_user_func_array($handler, $params);
            }
        } catch (Exception $e) {
            $this->handleRouteError($e);
        }
    }

    private function handleRouteError($e) {
        if (!headers_sent()) {
            http_response_code(500);
        }
        
        echo "<div style='background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border: 1px solid #f8bbd9; border-radius: 5px;'>";
        echo "<h3>🚨 Errore Route</h3>";
        echo "<p><strong>Messaggio:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }

    private function handleNotFound($uri = null, $method = null) {
        if ($this->debugMode && !headers_sent()) {
            $this->addDebugInfo("❌ 404 - Route non trovata");
            $this->printDebug();
        }

        if (!headers_sent()) {
            http_response_code(404);
        }
        
        echo "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>";
        echo "<h1 style='color: #666;'>404 - Pagina Non Trovata</h1>";
        echo "<p>La pagina richiesta <strong>" . htmlspecialchars($uri ?? 'sconosciuta') . "</strong> non esiste.</p>";
        
        $app = Application::getInstance();
        echo "<p><a href='" . $app->getFullUrl('/') . "' style='color: #007bff;'>🏠 Torna alla Home</a></p>";
        echo "<p><a href='" . $app->getFullUrl('/test') . "' style='color: #007bff;'>🧪 Test Sistema</a></p>";
        echo "<p><a href='" . $app->getFullUrl('/direct-login') . "' style='color: #007bff;'>🔐 Login Diretto</a></p>";
        echo "</div>";
    }

    private function addDebugInfo($message) {
        $this->debugOutput .= "• " . $message . "\n";
    }

    private function debugRoutes() {
        $this->addDebugInfo("=== ROUTE REGISTRATE ===");
        
        $this->addDebugInfo("Route principali:");
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $pattern => $handler) {
                $handlerString = is_callable($handler) && !is_string($handler) 
                    ? '[Closure/Function]' 
                    : (string)$handler;
                $this->addDebugInfo("  $method $pattern → $handlerString");
            }
        }
        
        $this->addDebugInfo("Route moduli:");
        foreach ($this->moduleRoutes as $method => $routes) {
            foreach ($routes as $pattern => $data) {
                $handlerString = is_callable($data['handler']) && !is_string($data['handler']) 
                    ? '[Closure/Function]' 
                    : (string)$data['handler'];
                $this->addDebugInfo("  $method $pattern → $handlerString (modulo: {$data['module']})");
            }
        }
    }

    private function printDebug() {
        if (!empty($this->debugOutput)) {
            echo "<div style='background: #e3f2fd; color: #0d47a1; padding: 15px; margin: 10px; border: 1px solid #90caf9; border-radius: 5px; font-family: monospace; white-space: pre-line; font-size: 12px;'>";
            echo "<h3 style='margin-top: 0; color: #1976d2;'>🔍 Debug Router</h3>";
            echo htmlspecialchars($this->debugOutput);
            echo "</div>";
        }
    }
}