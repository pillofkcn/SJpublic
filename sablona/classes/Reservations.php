<?php
require_once 'Db.php';

// definicia triedy
class Reservations extends Db {
    // konstruktor Reservations
    public function __construct() {
        // konstruktor rodica
        parent::__construct();
    }

    // vytvara novu rezervaciu
    public function addReservation($userId, $date) {
        $stmt = $this->pdo->prepare("INSERT INTO table_reservations (user_id, date) VALUES (?, ?)");
        return $stmt->execute([$userId, $date]);
    }

    // vrati vsetky rezervacie
    public function getReservations() {
        $stmt = $this->pdo->query("SELECT * FROM table_reservations");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // vrati rezervaciu na zaklade zadaneho id
    public function getReservationById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM table_reservations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // aktualizuje datum rezervacie zadaneho id
    public function updateReservation($id, $date) {
        $stmt = $this->pdo->prepare("UPDATE table_reservations SET date = ? WHERE id = ?");
        return $stmt->execute([$date, $id]);
    }

    // zmaze rezervaciu na zaklade zadaneho id
    public function deleteReservation($id) {
        $stmt = $this->pdo->prepare("DELETE FROM table_reservations WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
