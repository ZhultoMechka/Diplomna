<?php
// ============================================
// DATABASE CONNECTION CLASS
// ============================================
// File: api/config/database.php
// Purpose: Database connection and configuration

class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "klimatici_db";  // ВАЖНО: klimatici_db (не klimatici)
    private $username = "root";
    private $password = "qwe123";  // Твоята MySQL парола (ако имаш)
    private $charset = "utf8mb4";
    
    public $conn;
    
    /**
     * Get database connection
     * @return PDO|null Database connection object
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Create PDO connection
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password
            );
            
            // Set PDO attributes
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            return null;
        }
        
        return $this->conn;
    }
    
    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>