<?php

class Auth {
    private $pdo;

    public function __construct() {
        $host = '127.0.0.1';
        $db = 'sj';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_auth WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function register($username, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO table_auth (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $passwordHash]);
    }

    public function update($id, $username, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("UPDATE table_auth SET username = ?, email = ?, password = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $passwordHash, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM table_auth WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getUsers() {
        $stmt = $this->pdo->query("SELECT * FROM table_auth");
        return $stmt->fetchAll();
    }
}
?>
