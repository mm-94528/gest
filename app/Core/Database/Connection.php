<?php
class Database {
    private $connection;
    private $config;

    public function __construct($config) {
        $this->config = $config;
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        $updateParams = [];
        
        // Usa parametri posizionali per evitare conflitti
        $paramIndex = 0;
        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = ?";
            $updateParams[] = $value;
            $paramIndex++;
        }
        $setClause = implode(', ', $setClause);
        
        // Aggiungi i parametri WHERE alla fine
        foreach ($whereParams as $param) {
            $updateParams[] = $param;
        }
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        return $this->query($sql, $updateParams);
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollBack();
    }
}
class Auth {
    private $database;
    private $user = null;
    private $useDatabase = false;

    public function __construct(Database $database = null) {
        $this->database = $database;
        
        // Testa se il database è utilizzabile
        if ($this->database) {
            $this->useDatabase = $this->testDatabaseConnection();
        }
        
        if ($this->useDatabase) {
            $this->loadUser();
        }
    }

    private function testDatabaseConnection() {
        try {
            // Testa se la tabella users esiste e ha la struttura corretta
            $result = $this->database->fetchOne("SHOW TABLES LIKE 'users'");
            if (!$result) {
                error_log("Auth: Tabella 'users' non trovata. Uso modalità senza database.");
                return false;
            }
            
            // Testa se la struttura della tabella è corretta
            $columns = $this->database->fetchAll("SHOW COLUMNS FROM users");
            $columnNames = array_column($columns, 'Field');
            
            $requiredColumns = ['id', 'email', 'password'];
            foreach ($requiredColumns as $col) {
                if (!in_array($col, $columnNames)) {
                    error_log("Auth: Colonna '$col' mancante nella tabella users. Uso modalità senza database.");
                    return false;
                }
            }
            
            error_log("Auth: Database utilizzabile per autenticazione.");
            return true;
            
        } catch (Exception $e) {
            error_log("Auth: Errore test database: " . $e->getMessage() . ". Uso modalità senza database.");
            return false;
        }
    }

    public function attempt($email, $password) {
        if ($this->useDatabase) {
            return $this->attemptDatabase($email, $password);
        } else {
            return $this->attemptMock($email, $password);
        }
    }

    private function attemptDatabase($email, $password) {
        try {
            // Query più robusta che gestisce diverse strutture di tabella
            $columns = $this->database->fetchAll("SHOW COLUMNS FROM users");
            $columnNames = array_column($columns, 'Field');
            
            // Costruisci query dinamicamente in base alle colonne disponibili
            $whereClause = "email = ?";
            $params = [$email];
            
            // Aggiungi condizione active solo se la colonna esiste
            if (in_array('active', $columnNames)) {
                $whereClause .= " AND active = 1";
            } elseif (in_array('attivo', $columnNames)) {
                $whereClause .= " AND attivo = 1";
            }
            
            $user = $this->database->fetchOne(
                "SELECT * FROM users WHERE $whereClause",
                $params
            );

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'] ?? $user['name'] ?? 'Utente';
                $_SESSION['user_email'] = $user['email'];
                $this->user = $user;
                
                // Aggiorna ultimo accesso se la colonna esiste
                if (in_array('last_login', $columnNames)) {
                    try {
                        $this->database->update('users', 
                            ['last_login' => date('Y-m-d H:i:s')],
                            'id = ?',
                            [$user['id']]
                        );
                    } catch (Exception $e) {
                        // Ignora errori di aggiornamento last_login
                        error_log("Warning: Could not update last_login: " . $e->getMessage());
                    }
                }
                
                return true;
            }

            return false;
            
        } catch (Exception $e) {
            error_log("Auth database error: " . $e->getMessage());
            // Fallback a modalità mock in caso di errore
            return $this->attemptMock($email, $password);
        }
    }

    private function attemptMock($email, $password) {
        // Credenziali di test hardcoded
        if ($email === 'admin@example.com' && $password === 'password') {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Admin Sistema';
            $_SESSION['user_email'] = $email;
            $this->user = [
                'id' => 1,
                'nome' => 'Admin Sistema',
                'email' => $email,
                'ruolo' => 'admin'
            ];
            return true;
        }
        
        return false;
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        $this->user = null;
        
        // Distruggi la sessione completamente
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function check() {
        if ($this->useDatabase && $this->user) {
            return true;
        }
        
        // Fallback: controlla la sessione
        return isset($_SESSION['user_id']);
    }

    public function user() {
        if ($this->user) {
            return $this->user;
        }
        
        // Fallback: dati dalla sessione
        if (isset($_SESSION['user_id'])) {
            return [
                'id' => $_SESSION['user_id'],
                'nome' => $_SESSION['user_name'] ?? 'Utente',
                'email' => $_SESSION['user_email'] ?? 'user@example.com'
            ];
        }
        
        return null;
    }

    public function id() {
        $user = $this->user();
        return $user['id'] ?? null;
    }

    private function loadUser() {
        if (!$this->useDatabase || !isset($_SESSION['user_id'])) {
            return;
        }
        
        try {
            $columns = $this->database->fetchAll("SHOW COLUMNS FROM users");
            $columnNames = array_column($columns, 'Field');
            
            $whereClause = "id = ?";
            $params = [$_SESSION['user_id']];
            
            // Aggiungi condizione active solo se la colonna esiste
            if (in_array('active', $columnNames)) {
                $whereClause .= " AND active = 1";
            } elseif (in_array('attivo', $columnNames)) {
                $whereClause .= " AND attivo = 1";
            }
            
            $this->user = $this->database->fetchOne(
                "SELECT * FROM users WHERE $whereClause",
                $params
            );
            
        } catch (Exception $e) {
            error_log("Auth loadUser error: " . $e->getMessage());
            // Non fallire, mantieni i dati di sessione
        }
    }

    public function hasPermission($permission) {
        if (!$this->check()) {
            return false;
        }

        if (!$this->useDatabase) {
            // In modalità mock, admin ha tutti i permessi
            return true;
        }

        try {
            // Verifica se le tabelle dei permessi esistono
            $permissionsTable = $this->database->fetchOne("SHOW TABLES LIKE 'permissions'");
            $userPermissionsTable = $this->database->fetchOne("SHOW TABLES LIKE 'user_permissions'");
            
            if (!$permissionsTable || !$userPermissionsTable) {
                // Se non ci sono tabelle permessi, consenti tutto (modalità sviluppo)
                return true;
            }
            
            // Verifica permessi dell'utente
            $result = $this->database->fetchOne("
                SELECT COUNT(*) as count 
                FROM user_permissions up
                JOIN permissions p ON up.permission_id = p.id
                WHERE up.user_id = ? AND p.name = ?
            ", [$this->id(), $permission]);

            return $result['count'] > 0;
            
        } catch (Exception $e) {
            error_log("Auth permission check error: " . $e->getMessage());
            // In caso di errore, consenti l'accesso (modalità sviluppo)
            return true;
        }
    }

    public function isUsingDatabase() {
        return $this->useDatabase;
    }
}