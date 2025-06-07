<?php
// Abilita la visualizzazione degli errori per debug


// Autoloader semplice
spl_autoload_register(function ($class) {
    // Rimuovi il namespace base se presente
    $class = str_replace('App\\', '', $class);
    
    // Converti namespace in path
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // Percorsi possibili
    $paths = [
        __DIR__ . '/../app/' . $path . '.php',
        __DIR__ . '/../app/Core/' . $path . '.php',
        __DIR__ . '/../app/Models/' . $path . '.php',
        __DIR__ . '/../app/Controllers/' . $path . '.php'
    ];
    
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Carica manualmente le classi del core
$coreFiles = [
    '../app/Core/Application.php',
    '../app/Core/Database/Connection.php', 
    '../app/Core/Database/QueryBuilder.php',
    '../app/Core/Router/Router.php',
    '../app/Core/Http/Request.php',
    '../app/Core/Http/Response.php',
    
    '../app/Core/View/View.php',
    '../app/Core/Validation/Validator.php',
    '../app/Models/BaseModel.php',
    '../app/Controllers/BaseController.php',
    '../app/Controllers/AuthController.php',
    '../app/Controllers/DashboardController.php'
];

foreach ($coreFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        require_once $fullPath;
    } else {
        echo "ERRORE: File non trovato: $fullPath<br>";
    }
}

// Verifica che le directory di configurazione esistano
$configDir = __DIR__ . '/../config';
if (!is_dir($configDir)) {
    die('Directory config non trovata. Assicurati che la struttura del progetto sia corretta.');
}

try {
    // Avvia l'applicazione
    $app = Application::getInstance();
    $app->run();
    
} catch (Exception $e) {
    echo "<h1>Errore di Sistema</h1>";
    echo "<p><strong>Messaggio:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Linea:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
