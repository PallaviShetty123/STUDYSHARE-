<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

if (isStudentLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_no = sanitize($_POST['roll_no'] ?? '');
    $dob = $_POST['dob'] ?? '';

    if (empty($roll_no) || empty($dob)) {
        $errors[] = 'Please enter roll number and date of birth.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM students WHERE roll_no = ? AND dob = ?');
        $stmt->execute([$roll_no, $dob]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $errors[] = 'Invalid roll number or date of birth.';
        } else {
            setStudentSession($student);
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
    <title>Student Login | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="login-box">
            <h1>StudyShare</h1>
            <p class="subtitle">Student Portal</p>
            
            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="roll_no">Roll Number</label>
                    <input type="text" id="roll_no" name="roll_no" required placeholder="Enter your roll number">
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required placeholder="YYYY-MM-DD">
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="footer-link">
                <p>Are you an admin? <a href="../admin/login.php">Admin Portal</a></p>
            </div>
        </div>
    </div>
</body>
</html>
