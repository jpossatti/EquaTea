<?php
/**
 * Database.php
 * Classe de conexão com o banco de dados MySQL usando PDO.
 * Padrão Singleton para garantir uma única instância.
 */
class Database
{
    private static $instance = null;
    private $conn;
    
    private $host = 'localhost';
    private $dbname = 'equatea_db';
    private $username = 'root';
    private $password = '';
    
    private function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection()
    {
        return $this->conn;
    }
    
    public function query($sql, $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }
    
    public function commit()
    {
        return $this->conn->commit();
    }
    
    public function rollback()
    {
        return $this->conn->rollback();
    }
}