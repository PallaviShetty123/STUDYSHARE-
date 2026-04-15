<?php
session_start();
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/auth_lecturer.php';

if (isLecturerLoggedIn()) {
    redirect('../lecture/dashboard.php');
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
            redirect('../lecture/dashboard.php');
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
    <style>
        .auth-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
        }

        .login-box h1 {
            color: #4338ca;
            font-size: 2.2rem;
            margin-bottom: 0.4rem;
        }

        .subtitle {
            color: #6b7280;
            font-size: 0.95rem;
            margin-bottom: 1.8rem;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.25s ease;
        }

        .login-btn:hover {
            transform: translateY(-1px);
        }

        .back-link {
            text-align: center;
            margin-top: 18px;
            color: #6b7280;
            font-size: 0.95rem;
        }

        .back-link a {
            color: #4338ca;
            text-decoration: none;
            font-weight: 700;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="login-box">
            <h1>StudyShare</h1>
            <p class="subtitle">Lecturer Portal</p>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="back-link">
                <p>Student? <a href="../student/">Student Portal</a></p>
                <p>Admin? <a href="../admin/">Admin Panel</a></p>
            </div>
        </div>
    </div>
</body>
</html>
