<?php
require 'classes/Db.php';
require 'classes/Auth.php';
require 'classes/Reservations.php';

session_start();
$db = new Db();
$pdo = $db->getPdo();

$limit = 4; // Number of trainings to show per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Check if user is logged in
$userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
$username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : null;

$isAdmin = false;
if ($userId) {
    $stmt = $pdo->prepare("SELECT is_admin FROM table_auth WHERE id = ?");
    $stmt->bindParam(1, $userId);
    $stmt->execute();
    $isAdmin = $stmt->fetchColumn();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $userId) {
    if (isset($_POST['delete_training_id'])) {
        // Check if the user is the owner of the training or an admin
        $delete_training_id = $_POST['delete_training_id'];
        $stmt = $pdo->prepare("SELECT user_id FROM table_trainings WHERE id = ?");
        $stmt->bindParam(1, $delete_training_id);
        $stmt->execute();
        $training = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($training && ($training['user_id'] == $userId || $isAdmin)) {
            $stmt = $pdo->prepare("DELETE FROM table_trainings WHERE id = ?");
            $stmt->bindParam(1, $delete_training_id);
            $stmt->execute();
            header("Location: trainings.php?page=$page");
            exit;
        } else {
            echo "You are not authorized to delete this training.";
        }
    } else if (isset($_POST['training_id'])) {
        // Check if the user is the owner of the training or an admin
        $training_id = $_POST['training_id'];
        $stmt = $pdo->prepare("SELECT user_id FROM table_trainings WHERE id = ?");
        $stmt->bindParam(1, $training_id);
        $stmt->execute();
        $training = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($training && ($training['user_id'] == $userId || $isAdmin)) {
            // Update existing training
            $name = $_POST['name'];
            $equipment = $_POST['equipment'];
            $length = $_POST['length'];
            $instructions = $_POST['instructions'];

            $image = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $image = file_get_contents($_FILES['image']['tmp_name']);
            }

            if ($image) {
                $stmt = $pdo->prepare("UPDATE table_trainings SET name = ?, equipment = ?, length = ?, instructions = ?, author = ?, image = ? WHERE id = ?");
                $stmt->bindParam(1, $name);
                $stmt->bindParam(2, $equipment);
                $stmt->bindParam(3, $length);
                $stmt->bindParam(4, $instructions);
                $stmt->bindParam(5, $username); // Set author to the current username
                $stmt->bindParam(6, $image, PDO::PARAM_LOB);
                $stmt->bindParam(7, $training_id);
            } else {
                $stmt = $pdo->prepare("UPDATE table_trainings SET name = ?, equipment = ?, length = ?, instructions = ?, author = ? WHERE id = ?");
                $stmt->bindParam(1, $name);
                $stmt->bindParam(2, $equipment);
                $stmt->bindParam(3, $length);
                $stmt->bindParam(4, $instructions);
                $stmt->bindParam(5, $username); // Set author to the current username
                $stmt->bindParam(6, $training_id);
            }

            $stmt->execute();
            header("Location: trainings.php?page=$page");
            exit;
        } else {
            echo "You are not authorized to update this training.";
        }
    } else if (isset($_POST['name'])) {
        // Create new training
        $name = $_POST['name'];
        $equipment = $_POST['equipment'];
        $length = $_POST['length'];
        $instructions = $_POST['instructions'];

        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $image = file_get_contents($_FILES['image']['tmp_name']);
        }

        $stmt = $pdo->prepare("INSERT INTO table_trainings (name, equipment, length, instructions, author, image, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $equipment);
        $stmt->bindParam(3, $length);
        $stmt->bindParam(4, $instructions);
        $stmt->bindParam(5, $username); // Set author to the current username
        $stmt->bindParam(6, $image, PDO::PARAM_LOB);
        $stmt->bindParam(7, $userId);  // Ensure user_id is set
        $stmt->execute();
    }
}

// Fetch trainings for slideshow
$stmt = $pdo->prepare("SELECT * FROM table_trainings LIMIT ? OFFSET ?");
$stmt->bindParam(1, $limit, PDO::PARAM_INT);
$stmt->bindParam(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total number of trainings for pagination
$totalTrainings = $pdo->query("SELECT COUNT(*) FROM table_trainings")->fetchColumn();
$totalPages = ceil($totalTrainings / $limit);

$editTraining = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM table_trainings WHERE id = ?");
    $stmt->bindParam(1, $_GET['edit_id']);
    $stmt->execute();
    $editTraining = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trainings</title>
    <link rel="stylesheet" href="css/trainings.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include_once "comps/navbar.php"; ?>
    <div class="trainings-container">

        <div class="carousel-container">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="carousel-button prev-button">&#10094;</a>
            <?php endif; ?>
            <div class="carousel">
                <?php foreach ($trainings as $training): ?>
                    <div class="carousel-item">
                        <a href="?edit_id=<?= $training['id'] ?>&page=<?= $page ?>">
                            <img src="data:image/jpeg;base64,<?= base64_encode($training['image']) ?>" alt="<?= $training['name'] ?>">
                            <div class="carousel-caption">
                                <div class="training-name"><?= $training['name'] ?></div>
                                <div class="author-name"><?= $training['author'] ?></div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="carousel-button next-button">&#10095;</a>
            <?php endif; ?>
        </div>

        <?php if ($editTraining): ?>
            <?php if ($userId && ($editTraining['user_id'] == $userId || $isAdmin)): ?>
                <div class="training-form edit-form">
                    <h2>Aktualizázia Tréningu</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="training_id" value="<?= $editTraining['id'] ?>">
                        <input type="text" name="name" placeholder="Názov" value="<?= $editTraining['name'] ?>" required>
                        <textarea name="equipment" placeholder="Potrebné vybavenie" required><?= $editTraining['equipment'] ?></textarea>
                        <select name="length" required>
                            <?php for ($i = 15; $i <= 120; $i += 15): ?>
                                <option value="<?= $i ?>" <?= ($editTraining['length'] == $i) ? 'selected' : '' ?>><?= $i ?> minút</option>
                            <?php endfor; ?>
                        </select>
                        <textarea name="instructions" placeholder="Postup" required><?= $editTraining['instructions'] ?></textarea>
                        <input type="file" name="image">
                        <button type="submit">Aktualizuj</button>
                        <button type="submit" name="delete_training_id" value="<?= $editTraining['id'] ?>">Zmaž</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="training-form edit-form">
                    <h2>Prehľad Tréningu</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="training_id" value="<?= $editTraining['id'] ?>">
                        <input type="text" name="name" placeholder="Názov" value="<?= $editTraining['name'] ?>" disabled>
                        <textarea name="equipment" placeholder="Potrebné vybavenie" disabled><?= $editTraining['equipment'] ?></textarea>
                        <select name="length" disabled>
                            <?php for ($i = 15; $i <= 120; $i += 15): ?>
                                <option value="<?= $i ?>" <?= ($editTraining['length'] == $i) ? 'selected' : '' ?>><?= $i ?> minút</option>
                            <?php endfor; ?>
                        </select>
                        <textarea name="instructions" placeholder="Postup" disabled><?= $editTraining['instructions'] ?></textarea>
                    </form>
                </div>
            <?php endif; ?>
        <?php elseif ($userId): ?>
            <div class="training-form">
                <h2>Vytvorenie Tréningu</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="name" placeholder="Názov" required>
                    <textarea name="equipment" placeholder="Vybavenie" required></textarea>
                    <select name="length" required>
                        <?php for ($i = 15; $i <= 120; $i += 15): ?>
                            <option value="<?= $i ?>"><?= $i ?> minút</option>
                        <?php endfor; ?>
                    </select>
                    <textarea name="instructions" placeholder="Postup" required></textarea>
                    <input type="file" name="image">
                    <button type="submit">Vytvoriť</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php include_once "comps/footer.php"; ?>
</body>
</html>
