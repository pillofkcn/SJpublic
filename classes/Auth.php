<?php
require_once 'Db.php';

// definicia triedy
class Auth extends Db {
    // konstruktor Auth
    public function __construct() {
        // konstruktor rodica
        parent::__construct();
    }

    // uklada registracne udaje uzivatela do DB, hashuje heslo pomocou algoritmu bcrypt
    public function register($username, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO table_auth (username, email, password) VALUES (?, ?, ?)");
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $passwordHash);
        return $stmt->execute();
    }

    // ak najde uzivatela so zadanym username, overi heslo, ak je OK, vrati uzivatela
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_auth WHERE username = ?");
        $stmt->bindParam(1, $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // vrati uzivatela podla ID
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_auth WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // vrati vsetkych uzivatelov
    public function getUsers() {
        $stmt = $this->pdo->query("SELECT * FROM table_auth");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // aktualizuje uzivatela podla ID
    public function update($id, $username, $email, $password) {
        if ($password) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("UPDATE table_auth SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $passwordHash);
            $stmt->bindParam(4, $id);
        } else {
            $stmt = $this->pdo->prepare("UPDATE table_auth SET username = ?, email = ? WHERE id = ?");
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $id);
        }
        return $stmt->execute();
    }

    // zmaze uzivatela podla ID
    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            
            // zmazanie vsetkych treningov uzivatela
            $stmt = $this->pdo->prepare("DELETE FROM table_trainings WHERE user_id = ?");
            $stmt->bindParam(1, $id);
            $stmt->execute();

            // zmazanie vsetkych rezervacii uzivatela
            $stmt = $this->pdo->prepare("DELETE FROM table_reservations WHERE user_id = ?");
            $stmt->bindParam(1, $id);
            $stmt->execute();

            // zmazanie uzivatela
            $stmt = $this->pdo->prepare("DELETE FROM table_auth WHERE id = ?");
            $stmt->bindParam(1, $id);
            $stmt->execute();

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // skontroluje, ci je uzivatel prihlaseny
    public function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    // skontroluje, ci je uzivatel admin
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user']['is_admin'];
    }
}
?>
