<?php
require_once 'Db.php';

// definicia triedy
class Auth extends Db {
    // konstruktor Auth
    public function __construct() {
        // konstruktor rodica
        parent::__construct();
    }

    // uklada registracne udaje uzivatela do db, hashuje heslo pomocou algoritmu bcrypt
    public function register($username, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO table_auth (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $passwordHash]);
    }

    // ak najde usera so zadanym usernamom, overi heslo, ak je ok, vrati usera
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_auth WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // pre zadane id vrati usera
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_auth WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // vrati vsetkych userov
    public function getUsers() {
        $stmt = $this->pdo->query("SELECT * FROM table_auth");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // aktualizuje uzivatelske udaje v db
    public function update($id, $username, $email, $password) {
        // ak bolo zadane heslo, musi znova hashovat
        if ($password) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("UPDATE table_auth SET username = ?, email = ?, password = ? WHERE id = ?");
            return $stmt->execute([$username, $email, $passwordHash, $id]);
        // inak len update ostatnych udajov
        } else {
            $stmt = $this->pdo->prepare("UPDATE table_auth SET username = ?, email = ? WHERE id = ?");
            return $stmt->execute([$username, $email, $id]);
        }
    }

    // zmaze uzivatela podla zadaneho id
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM table_auth WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // skontroluje, ci je v session setnuty user (a teda ci je lognuty)
    public function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    // skontroluje, ci ma lognuty user stlpec is_admin nastaveny na 1
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user']['is_admin'];
    }
}
?>
