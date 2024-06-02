<?php
class Db {
    protected $pdo;

    public function __construct() {
        $dsn = 'mysql:host=localhost;dbname=sj';
        $username = 'root';
        $password = '';
        try {
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
