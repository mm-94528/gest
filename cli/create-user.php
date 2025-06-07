<?php
require_once __DIR__ . '/../app/Core/Application.php';
require_once __DIR__ . '/../app/Core/Database/Connection.php';

echo "=== Creazione Utente ===\n\n";

// Richiedi dati utente
$nome = readline("Nome: ");
$cognome = readline("Cognome: ");
$email = readline("Email: ");
$password = readline("Password: ");
$ruolo = readline("Ruolo (admin/manager/user) [user]: ") ?: 'user';

if (empty($nome) || empty($cognome) || empty($email) || empty($password)) {
    echo "Errore: Tutti i campi sono obbligatori\n";
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Errore: Email non valida\n";
    exit(1);
}

if (!in_array($ruolo, ['admin', 'manager', 'user'])) {
    echo "Errore: Ruolo non valido\n";
    exit(1);
}

try {
    $config = require __DIR__ . '/../config/database.php';
    $database = new Database($config);
    
    // Verifica se email esiste già
    $existing = $database->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        echo "Errore: Email già esistente\n";
        exit(1);
    }
    
    // Crea utente
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $userId = $database->insert('users', [
        'nome' => $nome,
        'cognome' => $cognome,
        'email' => $email,
        'password' => $hashedPassword,
        'ruolo' => $ruolo,
        'attivo' => 1
    ]);
    
    echo "Utente creato con successo! ID: {$userId}\n";
    
} catch (Exception $e) {
    echo "ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}