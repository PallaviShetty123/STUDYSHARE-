<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = 'Please enter username and password.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if (!$admin || md5($password) !== $admin['password']) {
            $errors[] = 'Invalid admin credentials.';
        } else {
            setAdminSession($admin);
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="login-box">
            <h1>Admin Portal</h1>
            <p class="subtitle">Manage StudyShare Platform</p>
            
            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter admin username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter admin password">
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="footer-link">
                <p>Looking for student login? <a href="../user/login.php">Student Portal</a></p>
            </div>
        </div>
    </div>
</body>
</html>
