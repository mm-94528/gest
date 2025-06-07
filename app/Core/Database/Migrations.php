// app/Core/Database/Migration.php - Sistema di migrazione
<?php
class Migration {
    private $database;
    private $migrationsPath;

    public function __construct(Database $database, $migrationsPath = 'resources/migrations') {
        $this->database = $database;
        $this->migrationsPath = $migrationsPath;
        $this->createMigrationsTable();
    }

    private function createMigrationsTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_migration (migration)
            )
        ";
        
        $this->database->query($sql);
    }

    public function run() {
        $executedMigrations = $this->getExecutedMigrations();
        $migrationFiles = $this->getMigrationFiles();
        
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.sql');
            
            if (!in_array($migrationName, $executedMigrations)) {
                echo "Executing migration: {$migrationName}\n";
                $this->executeMigration($file, $migrationName);
            }
        }
        
        echo "All migrations completed!\n";
    }

    private function getExecutedMigrations() {
        $result = $this->database->fetchAll("SELECT migration FROM migrations");
        return array_column($result, 'migration');
    }

    private function getMigrationFiles() {
        $files = glob($this->migrationsPath . '/*.sql');
        sort($files);
        return $files;
    }

    private function executeMigration($file, $migrationName) {
        try {
            $sql = file_get_contents($file);
            
            // Esegui ogni statement separatamente
            $statements = array_filter(
                array_map('trim', explode(';', $sql)), 
                function($stmt) { return !empty($stmt); }
            );
            
            $this->database->beginTransaction();
            
            foreach ($statements as $statement) {
                $this->database->query($statement);
            }
            
            // Registra la migrazione come eseguita
            $this->database->insert('migrations', ['migration' => $migrationName]);
            
            $this->database->commit();
            
        } catch (Exception $e) {
            $this->database->rollback();
            throw new Exception("Migration {$migrationName} failed: " . $e->getMessage());
        }
    }
}