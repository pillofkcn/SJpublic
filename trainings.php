<?php
require 'classes/Auth.php';
require 'classes/Trainings.php';

session_start();

$auth = new Auth();
$trainings = new Trainings();

// pocet treningov na jeden page v showcase
$limit = 4; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// overenie, ci je uzivatel  prihlaseny
$userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
$username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : null;
// overenie, ci je admin
$isAdmin = $auth->isAdmin();

// ak je user vlastnik treningu alebo admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $userId) {
    // moze zmazat trening
    if (isset($_POST['delete_training_id'])) {
        $trainingId = $_POST['delete_training_id'];
        if ($trainings->isOwnerOrAdmin($trainingId, $userId, $isAdmin)) {
            $trainings->deleteTraining($trainingId);
            header("Location: trainings.php?page=$page");
            exit;
        } else {
            echo "You are not authorized to delete this training.";
        }
    // moze updatnut trening
    } elseif (isset($_POST['training_id'])) {
        $trainingId = $_POST['training_id'];
        if ($trainings->isOwnerOrAdmin($trainingId, $userId, $isAdmin)) {
            $data = [
                'name' => $_POST['name'],
                'equipment' => $_POST['equipment'],
                'length' => $_POST['length'],
                'instructions' => $_POST['instructions'],
                'image' => isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK ? file_get_contents($_FILES['image']['tmp_name']) : null
            ];
            $trainings->updateTraining($data, $username, $trainingId);
            header("Location: trainings.php?page=$page");
            exit;
        } else {
            echo "You are not authorized to update this training.";
        }
    // moze vytvorit trening
    } else {
        $data = [
            'name' => $_POST['name'],
            'equipment' => $_POST['equipment'],
            'length' => $_POST['length'],
            'instructions' => $_POST['instructions'],
            'image' => isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK ? file_get_contents($_FILES['image']['tmp_name']) : null
        ];
        $trainings->createTraining($data, $username, $userId);
    }
}

// ziskanie treningov a poctu stran do showcasu
$trainingsList = $trainings->getTrainings($page);
$totalPages = $trainings->getTotalPages();

$editTraining = null;

// ak user klikne na trening, ziska sa trening na zaklade id
if (isset($_GET['edit_id'])) {
    $editTraining = $trainings->getTrainingById($_GET['edit_id']);
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
            <!-- ak je cislo page vacsie ako jedna, zobrazi sa prev button -->
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="carousel-button prev-button">&#10094;</a>
            <?php endif; ?>
            <div class="carousel">
                <!-- vykreslenie showcasu treningov -->
                <?php foreach ($trainingsList as $training): ?>
                    <div class="carousel-item">
                        <!-- link na edit -->
                        <a href="?edit_id=<?= $training['id'] ?>&page=<?= $page ?>">
                            <!-- obrazok -->
                            <img src="data:image/jpeg;base64,<?= base64_encode($training['image']) ?>" alt="<?= $training['name'] ?>">
                            <!-- nazov treningu a meno autora -->
                            <div class="carousel-caption">
                                <div class="training-name"><?= $training['name'] ?></div>
                                <div class="author-name"><?= $training['author'] ?></div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- ak je cislo page mensie ako pages dokopy, zobrazi sa next button -->
            <?php if ($page < $totalPages): ?>
                
                <a href="?page=<?= $page + 1 ?>" class="carousel-button next-button">&#10095;</a>
            <?php endif; ?>
        </div>

        <!-- ak user klikne na trening -->
        <?php if ($editTraining): ?>
            <!-- ak je prihlaseny user vlastnik treningu alebo admin, Aktualizacia -->
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

            <!-- ak user nie je prihlaseny alebo je prihlaseny ale nie je vlastnikom treningu, Preview -->
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

        <!-- ak nan neklikne a je prihlaseny, ma moznost vytvorit trening -->
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
