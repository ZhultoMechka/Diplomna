<?php

// database.php - Клас за връзка с базата данни

class Database {
    // базови настройки за връзка с базата данни
    private $host = "localhost";
    private $db_name = "klimatici_db";  // ВАЖНО: klimatici_db (не klimatici)
    private $username = "root";
    private $password = "qwe123";  //MySQL парола (ако имате)
    private $charset = "utf8mb4";
    
    public $conn;
    
    /**
     * Get database connection
     * @return PDO|null Database connection object
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // създаваме DSN (Data Source Name) за PDO връзката
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password
            );
            
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
     * Затваря връзката с базата данни
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>