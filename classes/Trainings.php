<?php
require_once 'Db.php';

// definicia triedy
class Trainings extends Db {
    // pre pocet okien v image showcase
    private $limit = 4;

    // konstruktor Trainigs
    public function __construct() {
        // konstruktor rodica
        parent::__construct();
    }

    // ziskanie treningov na zaklade zadanej stranky showcase
    public function getTrainings($page) {
        // vypocet stranky a offsetu zo ziskanych treningov pre zobrazenie v showcase
        // [ T1 T2 T3 T4 ] [ T5 ]
        //      page 1     page 2
        // SELECT * FROM table_trainings LIMIT 4 OFFSET 0 -> zacina sa T1
        $offset = ($page - 1) * $this->limit;
        $stmt = $this->getPdo()->prepare("SELECT * FROM table_trainings LIMIT ? OFFSET ?");
        $stmt->bindParam(1, $this->limit, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // vrati pocet stranok (zaokruhleny hore)
    public function getTotalPages() {
        $totalTrainings = $this->getPdo()->query("SELECT COUNT(*) FROM table_trainings")->fetchColumn();
        return ceil($totalTrainings / $this->limit);
    }

    // ziska trening na zaklade zadaneho id
    public function getTrainingById($id) {
        $stmt = $this->getPdo()->prepare("SELECT * FROM table_trainings WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // vytvorenie treningu
    public function createTraining($data, $username, $userId) {
        $stmt = $this->getPdo()->prepare("INSERT INTO table_trainings (name, equipment, length, instructions, author, image, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['equipment']);
        $stmt->bindParam(3, $data['length']);
        $stmt->bindParam(4, $data['instructions']);
        $stmt->bindParam(5, $username);
        $stmt->bindParam(6, $data['image'], PDO::PARAM_LOB);
        $stmt->bindParam(7, $userId);
        $stmt->execute();
    }

    // aktualizacia treningu
    public function updateTraining($data, $username, $trainingId) {
        // ak je zadany aj novy obrazok
        if ($data['image']) {
            $stmt = $this->getPdo()->prepare("UPDATE table_trainings SET name = ?, equipment = ?, length = ?, instructions, author = ?, image = ? WHERE id = ?");
            $stmt->bindParam(1, $data['name']);
            $stmt->bindParam(2, $data['equipment']);
            $stmt->bindParam(3, $data['length']);
            $stmt->bindParam(4, $data['instructions']);
            $stmt->bindParam(5, $username);
            $stmt->bindParam(6, $data['image'], PDO::PARAM_LOB);
            $stmt->bindParam(7, $trainingId);
        // ak zadany nie je
        } else {
            $stmt = $this->getPdo()->prepare("UPDATE table_trainings SET name = ?, equipment = ?, length = ?, instructions = ?, author = ? WHERE id = ?");
            $stmt->bindParam(1, $data['name']);
            $stmt->bindParam(2, $data['equipment']);
            $stmt->bindParam(3, $data['length']);
            $stmt->bindParam(4, $data['instructions']);
            $stmt->bindParam(5, $username);
            $stmt->bindParam(6, $trainingId);
        }
        $stmt->execute();
    }

    // zmazanie treningu na zaklade id
    public function deleteTraining($trainingId) {
        $stmt = $this->getPdo()->prepare("DELETE FROM table_trainings WHERE id = ?");
        $stmt->bindParam(1, $trainingId);
        $stmt->execute();
    }

    // zisti, ci je prihlaseny uzivatel vlastnikom treningu alebo admin
    public function isOwnerOrAdmin($trainingId, $userId, $isAdmin) {
        $stmt = $this->getPdo()->prepare("SELECT user_id FROM table_trainings WHERE id = ?");
        $stmt->bindParam(1, $trainingId);
        $stmt->execute();
        $training = $stmt->fetch(PDO::FETCH_ASSOC);

        return $training && ($training['user_id'] == $userId || $isAdmin);
    }
}
?>
