<?php
class AuthClass {
    private $pdo;

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

    public function register($username, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO table_auth (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $passwordHash]);
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_auth WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_auth WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUsers() {
        $stmt = $this->pdo->query("SELECT * FROM table_auth");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $username, $email, $password) {
        if ($password) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("UPDATE table_auth SET username = ?, email = ?, password = ? WHERE id = ?");
            return $stmt->execute([$username, $email, $passwordHash, $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE table_auth SET username = ?, email = ? WHERE id = ?");
            return $stmt->execute([$username, $email, $id]);
        }
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM table_auth WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
