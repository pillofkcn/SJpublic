<?php
session_start();

// Database connection using PDO
function getPDO() {
    $host = '127.0.0.1';
    $db = 'sj';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }

    return $pdo;
}

// Handle CRUD operations and login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo = getPDO();
    
    // Login
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $stmt = $pdo->prepare("SELECT * FROM table_auth WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        } else {
            echo "Invalid login credentials.";
        }
    }
    
    // Create (Register)
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO table_auth (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
    }

    // Update
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE table_auth SET username = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $email, $password, $id]);
    }

    // Delete
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM table_auth WHERE id = ?");
        $stmt->execute([$id]);
    }
}

// Fetch users
$pdo = getPDO();
$stmt = $pdo->query("SELECT * FROM table_auth");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Application</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <header class="container main-header">
        <div>
            <a href="index.php">
                <img src="img/logo.png" height="90">
            </a>
        </div>
        <nav class="main-nav">
            <ul class="main-menu" id="main-menu">
                <li><a href="index.php">Domov</a></li>
                <li><a href="reservations.php">Kalendár</a></li>
                <li><a href="jedalnicek.php">Jedálničky</a></li>
                <li><a href="treningovy_plan.php">Tréningové plány</a></li>
                <li><a href="qna.php">Q&A</a></li>
                <li><a href="kontakt.php">Kontakt</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li>Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?></li>
                    <li><a href="?logout=true">Logout</a></li>
                <?php else: ?>
                    <li><a href="auth.php">Login</a></li>
                    <li><a href="auth.php">Registrácia</a></li>
                <?php endif; ?>
            </ul>
            <a class="hamburger" id="hamburger">
                <i class="fa fa-bars"></i>
            </a>
        </nav>
    </header>

    <?php
    // Handle logout
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
    ?>

    <div class="form-container">
        <form action="" method="post">
            <h2>Login</h2>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" name="login" value="Login">
        </form>

        <form action="" method="post">
            <h2>Register</h2>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" name="register" value="Register">
        </form>
    </div>

    <h2>Users</h2>
    <div class="user-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <form action="" method="post" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <input type="password" name="password" placeholder="New password">
                            <input type="submit" name="update" value="Update">
                        </form>
                        <form action="" method="post" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <input type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure?')">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
