CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    ruolo ENUM('admin', 'manager', 'user') DEFAULT 'user',
    attivo BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_attivo (attivo),
    INDEX idx_ruolo (ruolo)
);

-- Inserisci utente admin di default
INSERT INTO users (nome, cognome, email, password, ruolo) 
VALUES ('Admin', 'Sistema', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Tabella permessi
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255),
    module VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabella associazione utenti-permessi
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_permission (user_id, permission_id)
);

-- Inserisci permessi base
INSERT INTO permissions (name, description, module) VALUES
('users.view', 'Visualizza utenti', 'admin'),
('users.create', 'Crea utenti', 'admin'),
('users.edit', 'Modifica utenti', 'admin'),
('users.delete', 'Elimina utenti', 'admin'),
('dashboard.view', 'Visualizza dashboard', 'core'),
('reports.view', 'Visualizza report', 'reports'),
('reports.export', 'Esporta report', 'reports')
ON DUPLICATE KEY UPDATE name=name;