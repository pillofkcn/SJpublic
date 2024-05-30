<?php
class ReservationsClass {
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

    public function addReservation($userId, $date) {
        $stmt = $this->pdo->prepare("INSERT INTO table_reservations (user_id, date) VALUES (?, ?)");
        return $stmt->execute([$userId, $date]);
    }

    public function getReservations() {
        $stmt = $this->pdo->query("SELECT * FROM table_reservations");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservationsByDate($date) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_reservations WHERE date = ?");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateReservation($id, $date) {
        $stmt = $this->pdo->prepare("UPDATE table_reservations SET date = ? WHERE id = ?");
        return $stmt->execute([$date, $id]);
    }

    public function deleteReservation($id) {
        $stmt = $this->pdo->prepare("DELETE FROM table_reservations WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
