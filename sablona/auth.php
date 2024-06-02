<?php
require_once 'classes/Auth.php';

session_start();

$auth = new Auth();
$error = '';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

// Handle CRUD operations and login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Login
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $user = $auth->login($username, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            header('Location: auth.php');
            exit;
        } else {
            $error = "Invalid login credentials.";
        }
    }
    
    // Create (Register)
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $terms = isset($_POST['terms']);

        if ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } elseif (!$terms) {
            $error = "You must agree to the terms.";
        } else {
            $auth->register($username, $email, $password);
        }
    }

    // Update
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $auth->update($id, $username, $email, $password);
    }

    // Delete
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $auth->delete($id);
    }
}

// Fetch users
if (isset($_SESSION['user']) && $_SESSION['user']['is_admin'] == 1) {
    $users = $auth->getUsers(); // Admins see all users
} else if (isset($_SESSION['user'])) {
    $users = [$auth->getUserById($_SESSION['user']['id'])]; // Registered users see only their own info
} else {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Application</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <script src="js/auth.js"></script>
</head>
<body>
    <?php include_once "comps/navbar.php"; ?>

    <?php if ($error): ?>
        <div class="error-container">
            <div class="error">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <!-- Forms for login, registration, and CRUD operations -->
        <form action="" method="post">
            <h2>Prihlásenie</h2>
            <input type="text" name="username" placeholder="Prezývka" required>
            <input type="password" name="password" placeholder="Heslo" required>
            <input type="submit" name="login" value="Prihlás ma">
        </form>
        <form action="" method="post" onsubmit="return validatePassword()">
            <h2>Registrácia</h2>
            <input type="text" name="username" placeholder="Prezývka" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" id="password" placeholder="Heslo" required>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Heslo znovu" required>
            <label>
                <input type="checkbox" name="terms" required>
                Súhlasím s podmienkami.
            </label>
            <input type="submit" name="register" value="Registruj ma">
        </form>
    </div>

    <?php if (!empty($users)): ?>
        <div class="user-panel">
            <h3><?php echo $_SESSION['user']['is_admin'] == 1 ? 'Admin Panel' : 'User Panel'; ?></h3>
            <table class="user-table">
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
                                <?php if ($_SESSION['user']['is_admin'] == 1 || $_SESSION['user']['id'] == $user['id']): ?>
                                    <input type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure?')">
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php include_once "comps/footer.php"; ?>
</body>
</html>
