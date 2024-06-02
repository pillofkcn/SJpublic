<?php
require_once 'Db.php';

class Trainings extends Db {
    private $limit = 4;

    public function __construct() {
        parent::__construct();
    }

    public function getTrainings($page) {
        $offset = ($page - 1) * $this->limit;
        $stmt = $this->getPdo()->prepare("SELECT * FROM table_trainings LIMIT ? OFFSET ?");
        $stmt->bindParam(1, $this->limit, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalPages() {
        $totalTrainings = $this->getPdo()->query("SELECT COUNT(*) FROM table_trainings")->fetchColumn();
        return ceil($totalTrainings / $this->limit);
    }

    public function getTrainingById($id) {
        $stmt = $this->getPdo()->prepare("SELECT * FROM table_trainings WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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

    public function updateTraining($data, $username, $trainingId) {
        if ($data['image']) {
            $stmt = $this->getPdo()->prepare("UPDATE table_trainings SET name = ?, equipment = ?, length = ?, instructions, author = ?, image = ? WHERE id = ?");
            $stmt->bindParam(1, $data['name']);
            $stmt->bindParam(2, $data['equipment']);
            $stmt->bindParam(3, $data['length']);
            $stmt->bindParam(4, $data['instructions']);
            $stmt->bindParam(5, $username);
            $stmt->bindParam(6, $data['image'], PDO::PARAM_LOB);
            $stmt->bindParam(7, $trainingId);
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

    public function deleteTraining($trainingId) {
        $stmt = $this->getPdo()->prepare("DELETE FROM table_trainings WHERE id = ?");
        $stmt->bindParam(1, $trainingId);
        $stmt->execute();
    }

    public function isOwnerOrAdmin($trainingId, $userId, $isAdmin) {
        $stmt = $this->getPdo()->prepare("SELECT user_id FROM table_trainings WHERE id = ?");
        $stmt->bindParam(1, $trainingId);
        $stmt->execute();
        $training = $stmt->fetch(PDO::FETCH_ASSOC);

        return $training && ($training['user_id'] == $userId || $isAdmin);
    }
}
?>
