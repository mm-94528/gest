<?php
// app/Core/Application.php
class Application {
    private static $instance = null;
    private $router;
    private $database;
    private $config;
    private $modules = [];
    private $container = [];

    private function __construct() {
        $this->loadEnvironment();
        $this->loadConfiguration();
        $this->initializeDatabase();
        $this->initializeRouter();
        $this->loadModules();
        $this->initializeAuth();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function run() {
        try {
            // Avvia la sessione
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Dispatch della richiesta
            $this->router->dispatch();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function loadEnvironment() {
        if (file_exists('.env')) {
            $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
    }

    private function loadConfiguration() {
        $this->config = [
            'database' => require '../config/database.php',
            'app' => require '../config/app.php',
            'modules' => require '../config/modules.php'
        ];
    }

    private function initializeDatabase() {
        $this->database = new Database($this->config['database']);
        $this->container['database'] = $this->database;
    }

    private function initializeRouter() {
        $this->router = new Router();
        $this->container['router'] = $this->router;
    }

    private function loadModules() {
        foreach ($this->config['modules'] as $moduleName => $moduleConfig) {
            if ($moduleConfig['enabled'] ?? true) {
                $this->registerModule($moduleName, $moduleConfig);
            }
        }
    }

    private function registerModule($name, $config) {
        // Carica le routes del modulo
        if (isset($config['routes_file']) && file_exists($config['routes_file'])) {
            $routes = require $config['routes_file'];
            $this->router->addModuleRoutes($name, $routes);
        }

        $this->modules[$name] = $config;
    }

    private function initializeAuth() {
        $this->container['auth'] = new Auth($this->database);
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
        
        if ($this->getConfig('app.debug')) {
            echo "<pre>";
            echo "Error: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . "\n";
            echo "Line: " . $e->getLine() . "\n";
            echo "Trace:\n" . $e->getTraceAsString();
            echo "</pre>";
        } else {
            http_response_code(500);
            include 'resources/views/errors/500.php';
        }
    }
}