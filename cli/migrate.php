<?php
require_once __DIR__ . '/../app/Core/Application.php';
require_once __DIR__ . '/../app/Core/Database/Connection.php';
require_once __DIR__ . '/../app/Core/Database/Migration.php';

echo "=== Sistema di Migrazione CRM/ERP ===\n\n";

try {
    // Carica configurazione
    $config = require __DIR__ . '/../config/database.php';
    
    // Connetti al database
    $database = new Database($config);
    echo "Connessione al database: OK\n";
    
    // Esegui migrazioni
    $migration = new Migration($database);
    $migration->run();
    
    echo "\nMigrazioni completate con successo!\n";
    
} catch (Exception $e) {
    echo "ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}