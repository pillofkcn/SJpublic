<?php
class Db {
    protected $pdo;

    // konstruktor
    public function __construct() {
        // udaje na pripojenie do db
        $dsn = 'mysql:host=localhost;dbname=sj';
        $username = 'root';
        $password = '';
        
        try {
            //PDO object for interaction with db (connection,SQL)
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getPdo() {
        return $this->pdo;
    }
}
?>
