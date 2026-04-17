<?php
session_start();
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/auth_lecturer.php';

if (isLecturerLoggedIn()) {
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
        $stmt = $pdo->prepare('SELECT * FROM lecturers WHERE username = ?');
        $stmt->execute([$username]);
        $lecturer = $stmt->fetch();

        if (!$lecturer || md5($password) !== $lecturer['password']) {
            $errors[] = 'Invalid username or password.';
        } else {
            setLecturerSession($lecturer);
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
    <title>Lecturer Login | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="brand">
                <div class="brand-mark">SS</div>
                <div>
                    <h1>StudyShare</h1>
                    <p>Lecturer Portal</p>
                </div>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form" autocomplete="on">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="footer-text">
                <p>Are you a student? <a href="../student/">Go to Student Portal</a></p>
                <p>Admin? <a href="../admin/">Go to Admin Panel</a></p>
            </div>
        </div>
    </div>
</body>
</html>
