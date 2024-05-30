<?php
session_start();
require_once 'AuthClass.php';

$auth = new Auth();

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
        $password = $_POST['password'];
        $auth->register($username, $email, $password);
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
$users = $auth->getUsers();
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
    <?php include_once "comps/navbar.php"; ?>

    <div class="form-container">
        <form action="" method="post">
            <h3>Login</h3>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" name="login" value="Login">
        </form>

        <form action="" method="post">
            <h3>Register</h3>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" name="register" value="Register">
        </form>
    </div>

    <div class="user-table">
        <h3>Admin Panel</h3>
    </div>
    <div class="user-table">
        <table class="center-table">
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
    <?php include_once "comps/footer.php"; ?>
</body>
</html>
