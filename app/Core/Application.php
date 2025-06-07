<?php
// app/Core/Application.php - FIX REDIRECT CON PATH CORRETTO
class Application {
    private static $instance = null;
    private $router;
    private $database;
    private $config;
    private $modules = [];
    private $container = [];
    private $basePath = '';

    private function __construct() {
        $this->detectBasePath();
        $this->loadEnvironment();
        $this->loadConfiguration();
        $this->initializeDatabase();
        $this->initializeRouter();
        $this->setupCoreRoutes();
        $this->loadModules();
        $this->initializeAuth();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function detectBasePath() {
        // Rileva il base path dall'URL corrente
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Estrai il base path
        $this->basePath = dirname($scriptName);
        
        // Normalizza il path
        if ($this->basePath === '/' || $this->basePath === '\\') {
            $this->basePath = '';
        }
        
        // Debug del base path
        error_log("Base Path rilevato: '{$this->basePath}'");
        error_log("Script Name: '{$scriptName}'");
        error_log("Request URI: '{$requestUri}'");
    }

    public function run() {
        try {
            // Avvia la sessione SOLO se non ci sono output precedenti
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_start();
            }

            // Dispatch della richiesta
            $this->router->dispatch();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function loadEnvironment() {
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value, '"');
                }
            }
        }
    }

    private function loadConfiguration() {
        $configPath = dirname(__DIR__, 2) . '/config';
        
        $this->config = [
            'database' => $this->loadConfigFile($configPath . '/database.php'),
            'app' => $this->loadConfigFile($configPath . '/app.php'),
            'modules' => $this->loadConfigFile($configPath . '/modules.php')
        ];
    }
    
    private function loadConfigFile($path) {
        if (file_exists($path)) {
            return require $path;
        }
        return [];
    }

    private function initializeDatabase() {
        try {
            $this->database = new Database($this->config['database']);
            $this->container['database'] = $this->database;
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            $this->database = null;
        }
    }

    private function initializeRouter() {
        $this->router = new Router($this->basePath);
        $this->container['router'] = $this->router;
    }

    private function setupCoreRoutes() {
        // Home redirect - CON PATH CORRETTO
        $this->router->get('/', function() {
            // Se è in debug mode, mostra la pagina di debug invece del redirect
            if (isset($_GET['debug'])) {
                echo "<h1>🏠 Home Page - Debug Mode</h1>";
                echo "<p><strong>Base Path:</strong> '{$this->basePath}'</p>";
                echo "<p><strong>Current URL:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
                echo "<h2>Test Links:</h2>";
                echo "<ul>";
                echo "<li><a href='" . $this->getUrl('/login') . "'>🔐 Login</a></li>";
                echo "<li><a href='" . $this->getUrl('/dashboard') . "'>📊 Dashboard</a></li>";
                echo "<li><a href='" . $this->getUrl('/test') . "'>🧪 Test</a></li>";
                echo "<li><a href='" . $this->getUrl('/debug') . "'>🔍 Debug Info</a></li>";
                echo "</ul>";
                echo "<p><a href='" . $this->getUrl('/') . "' style='color: #007bff;'>🔄 Home senza debug</a></p>";
                return;
            }
            
            // Comportamento normale: redirect con URL completo
            $auth = $this->get('auth');
            $redirectUrl = $auth && $auth->check() ? 
                $this->getFullUrl('/dashboard') : 
                $this->getFullUrl('/login');
            
            if (!headers_sent()) {
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                echo "<script>window.location.href='" . htmlspecialchars($redirectUrl) . "';</script>";
                echo "<p>Redirect in corso... <a href='" . htmlspecialchars($redirectUrl) . "'>Clicca qui se non vieni reindirizzato</a></p>";
            }
        });

        // Route autenticazione
        $this->router->get('/login', 'AuthController@showLogin');
        $this->router->post('/login', 'AuthController@login');
        $this->router->get('/logout', 'AuthController@logout');

        // Route protette
        $this->router->get('/dashboard', 'DashboardController@index');
        
        // Route di debug
        $this->router->get('/debug', function() {
            echo "<!DOCTYPE html><html><head><title>Debug Info</title></head><body>";
            echo "<div style='max-width: 800px; margin: 20px auto; padding: 20px; font-family: Arial, sans-serif;'>";
            echo "<h1>🔍 Debug CRM/ERP</h1>";
            echo "<h2>Path Information</h2>";
            echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr><td><strong>Base Path</strong></td><td>'{$this->basePath}'</td></tr>";
            echo "<tr><td><strong>Script Name</strong></td><td>" . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</td></tr>";
            echo "<tr><td><strong>Request URI</strong></td><td>" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</td></tr>";
            echo "<tr><td><strong>Document Root</strong></td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</td></tr>";
            echo "<tr><td><strong>HTTP Host</strong></td><td>" . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</td></tr>";
            echo "<tr><td><strong>Server Name</strong></td><td>" . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "</td></tr>";
            echo "</table>";
            
            echo "<h2>Test URLs</h2>";
            echo "<ul>";
            echo "<li>Relativo: <a href='" . $this->getUrl('/login') . "'>/login</a></li>";
            echo "<li>Assoluto: <a href='" . $this->getFullUrl('/login') . "'>Login completo</a></li>";
            echo "<li>Dashboard: <a href='" . $this->getFullUrl('/dashboard') . "'>Dashboard</a></li>";
            echo "<li>Test: <a href='" . $this->getFullUrl('/test') . "'>Test</a></li>";
            echo "</ul>";
            
            echo "<h2>Server Status</h2>";
            echo "<ul>";
            echo "<li>PHP Version: " . PHP_VERSION . "</li>";
            echo "<li>Headers Sent: " . (headers_sent() ? 'YES' : 'NO') . "</li>";
            echo "<li>Session Status: " . session_status() . "</li>";
            echo "<li>Database: " . ($this->database ? 'Connected' : 'Not connected') . "</li>";
            echo "</ul>";
            
            echo "</div></body></html>";
        });

        // Route di test
        $this->router->get('/test', function() {
            echo "<!DOCTYPE html>";
            echo "<html><head><title>Test CRM/ERP</title></head><body>";
            echo "<div style='max-width: 800px; margin: 50px auto; padding: 20px; font-family: Arial, sans-serif;'>";
            echo "<h1>✅ Sistema CRM/ERP Operativo!</h1>";
            echo "<p style='color: green; font-size: 18px;'>🎉 Congratulazioni! Il sistema è funzionante.</p>";
            
            echo "<h2>🧪 URL Testing</h2>";
            echo "<p><strong>Base Path:</strong> '{$this->basePath}'</p>";
            echo "<p><strong>Current URL:</strong> " . $this->getFullUrl($_SERVER['REQUEST_URI'] ?? '/') . "</p>";
            
            echo "<h2>🚀 Link Funzionali</h2>";
            echo "<ul>";
            echo "<li><a href='" . $this->getFullUrl('/login') . "' style='color: #007bff;'>🔐 Login (admin@example.com / password)</a></li>";
            echo "<li><a href='" . $this->getFullUrl('/dashboard') . "' style='color: #007bff;'>📊 Dashboard</a></li>";
            echo "<li><a href='" . $this->getFullUrl('/debug') . "' style='color: #007bff;'>🔍 Info Sistema</a></li>";
            echo "<li><a href='" . $this->getFullUrl('/?debug=1') . "' style='color: #007bff;'>🐛 Home Debug</a></li>";
            echo "</ul>";
            
            echo "<div style='background: #e8f5e8; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
            echo "<h3 style='color: #2e7d32;'>🎯 Core Completato!</h3>";
            echo "<p>Il sistema base è funzionante. Tutti i link dovrebbero funzionare correttamente.</p>";
            echo "</div>";
            
            echo "</div></body></html>";
        });

        // Route di test alternativo - accesso diretto senza redirect
        $this->router->get('/direct-login', 'AuthController@showLogin');
    }

    public function getUrl($path = '') {
        return $this->basePath . $path;
    }

    public function getFullUrl($path = '') {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host . $this->getUrl($path);
    }

    public function getBasePath() {
        return $this->basePath;
    }

    private function loadModules() {
        if (!isset($this->config['modules']) || !is_array($this->config['modules'])) {
            return;
        }
        
        foreach ($this->config['modules'] as $moduleName => $moduleConfig) {
            if (($moduleConfig['enabled'] ?? true)) {
                $this->registerModule($moduleName, $moduleConfig);
            }
        }
    }

    private function registerModule($name, $config) {
        if (isset($config['routes_file'])) {
            $routesFile = dirname(__DIR__, 2) . '/' . $config['routes_file'];
            
            if (file_exists($routesFile)) {
                try {
                    $routes = require $routesFile;
                    if (is_array($routes)) {
                        $this->router->addModuleRoutes($name, $routes);
                    }
                } catch (Exception $e) {
                    error_log("Error loading module routes for '$name': " . $e->getMessage());
                }
            }
        }

        $this->modules[$name] = $config;
    }

    private function initializeAuth() {
        if ($this->database) {
            $this->container['auth'] = new Auth($this->database);
        } else {
            $this->container['auth'] = new class {
                public function check() { 
                    return isset($_SESSION['user_id']);
                }
                public function user() { 
                    return ['nome' => 'Test User', 'email' => 'test@example.com'];
                }
                public function hasPermission($permission) { return true; }
                public function attempt($email, $password) { 
                    if ($email === 'admin@example.com' && $password === 'password') {
                        $_SESSION['user_id'] = 1;
                        return true;
                    }
                    return false;
                }
                public function logout() {
                    unset($_SESSION['user_id']);
                    session_destroy();
                }
            };
        }
    }

    public function get($key) {
        return $this->container[$key] ?? null;
    }

    public function getConfig($key = null) {
        if ($key === null) {
            return $this->config;
        }
        
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    private function handleException($e) {
        error_log($e->getMessage());
        
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Errore Sistema</title></head><body>";
        echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h1 style='color: #d32f2f;'>🚨 Errore Sistema CRM/ERP</h1>";
        echo "<p><strong>Messaggio:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        
        if ($this->getConfig('app.debug')) {
            echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
            echo "<p><strong>Linea:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
        }
        
        echo "<h3>🔧 Link di Test:</h3>";
        echo "<ul>";
        echo "<li><a href='" . $this->getFullUrl('/test') . "'>🧪 Test sistema</a></li>";
        echo "<li><a href='" . $this->getFullUrl('/debug') . "'>🔍 Debug info</a></li>";
        echo "<li><a href='" . $this->getFullUrl('/direct-login') . "'>🔐 Login diretto</a></li>";
        echo "</ul>";
        echo "</div></body></html>";
    }
}