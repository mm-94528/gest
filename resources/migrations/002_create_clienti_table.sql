CREATE TABLE IF NOT EXISTS clienti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codice_cliente VARCHAR(20) UNIQUE,
    ragione_sociale VARCHAR(255) NOT NULL,
    partita_iva VARCHAR(20),
    codice_fiscale VARCHAR(20),
    
    -- Indirizzo sede legale
    indirizzo VARCHAR(255),
    citta VARCHAR(100),
    provincia VARCHAR(5),
    cap VARCHAR(10),
    paese VARCHAR(100) DEFAULT 'Italia',
    
    -- Contatti
    telefono VARCHAR(20),
    email VARCHAR(255),
    pec VARCHAR(255),
    sito_web VARCHAR(255),
    
    -- Dati commerciali
    categoria ENUM('privato', 'azienda', 'pubblica_amministrazione') DEFAULT 'azienda',
    settore VARCHAR(100),
    dipendenti INT,
    fatturato_annuo DECIMAL(15,2),
    
    -- Condizioni commerciali
    pagamento_giorni INT DEFAULT 30,
    sconto_percentuale DECIMAL(5,2) DEFAULT 0,
    fido_massimo DECIMAL(12,2) DEFAULT 0,
    
    -- Referente principale
    referente_nome VARCHAR(100),
    referente_telefono VARCHAR(20),
    referente_email VARCHAR(255),
    
    -- Metadati
    note TEXT,
    attivo BOOLEAN DEFAULT TRUE,
    data_inserimento DATE,
    utente_inserimento INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indici
    INDEX idx_ragione_sociale (ragione_sociale),
    INDEX idx_partita_iva (partita_iva),
    INDEX idx_email (email),
    INDEX idx_attivo (attivo),
    INDEX idx_categoria (categoria),
    INDEX idx_data_inserimento (data_inserimento),
    
    -- Vincoli
    CONSTRAINT chk_partita_iva CHECK (partita_iva IS NULL OR LENGTH(partita_iva) = 11),
    CONSTRAINT chk_codice_fiscale CHECK (codice_fiscale IS NULL OR LENGTH(codice_fiscale) = 16),
    
    FOREIGN KEY (utente_inserimento) REFERENCES users(id)
);

-- Trigger per generare codice cliente automatico
DELIMITER $
CREATE TRIGGER before_clienti_insert 
BEFORE INSERT ON clienti 
FOR EACH ROW 
BEGIN
    IF NEW.codice_cliente IS NULL THEN
        SET NEW.codice_cliente = CONCAT('CLI', LPAD(LAST_INSERT_ID() + 1, 6, '0'));
    END IF;
    
    IF NEW.data_inserimento IS NULL THEN
        SET NEW.data_inserimento = CURDATE();
    END IF;
END$
DELIMITER ;

-- Tabella contatti aggiuntivi
CREATE TABLE IF NOT EXISTS contatti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    ruolo VARCHAR(100),
    telefono VARCHAR(20),
    cellulare VARCHAR(20),
    email VARCHAR(255),
    note TEXT,
    principale BOOLEAN DEFAULT FALSE,
    attivo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cliente_id) REFERENCES clienti(id) ON DELETE CASCADE,
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_email (email),
    INDEX idx_principale (principale)
);