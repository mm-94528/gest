<?php
// config/app.php
return [
    'name' => $_ENV['APP_NAME'] ?? 'CRM/ERP',
    'url' => $_ENV['APP_URL'] ?? 'http://mm.local',
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Europe/Rome',
    'locale' => $_ENV['APP_LOCALE'] ?? 'it_IT',
    'version' => '1.0.0',
    
    // Configurazioni di sicurezza
    'session' => [
        'lifetime' => 120, // minuti
        'cookie_secure' => $_ENV['SESSION_SECURE'] ?? false,
        'cookie_httponly' => true,
    ],
    
    // Upload files
    'upload' => [
        'max_size' => $_ENV['UPLOAD_MAX_SIZE'] ?? '10M',
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'path' => 'storage/uploads/',
    ],
    
    // Logging
    'log' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'debug',
        'path' => 'storage/logs/',
    ],
];