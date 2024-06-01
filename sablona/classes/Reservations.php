<?php
require_once 'Db.php';

class Reservations extends Db {
    public function __construct() {
        parent::__construct();
    }

    public function addReservation($userId, $date) {
        $stmt = $this->pdo->prepare("INSERT INTO table_reservations (user_id, date) VALUES (?, ?)");
        return $stmt->execute([$userId, $date]);
    }

    public function getReservations() {
        $stmt = $this->pdo->query("SELECT * FROM table_reservations");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservationById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_reservations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
