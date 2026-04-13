<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $semester = intval($_POST['semester'] ?? 0);
    
    if (empty($subject) || empty($description) || empty($department) || $semester === 0) {
        $errors[] = 'Please fill all required fields.';
    } elseif (empty($_FILES['file']['name'])) {
        $errors[] = 'Please upload a file.';
    } elseif (!isValidFileUpload($_FILES['file']['name'])) {
        $errors[] = 'Invalid file type. Only PDF and DOC files are allowed.';
    } elseif ($_FILES['file']['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File size exceeds 10MB limit.';
    } else {
        $filename = uploadFile($_FILES['file'], NOTES_DIR);
        if ($filename) {
            $pdo = db();
            $stmt = $pdo->prepare('INSERT INTO notes (subject, description, file_path, department, semester) VALUES (?, ?, ?, ?, ?)');
            if ($stmt->execute([$subject, $description, $filename, $department, $semester])) {
                $success = 'Note uploaded successfully!';
                // Clear form
                $_POST = [];
            } else {
                $errors[] = 'Failed to save note to database.';
            }
        } else {
            $errors[] = 'Failed to upload file.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Notes | StudyShare Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <h2>StudyShare</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="upload_notes.php" class="nav-item active">
                    <span class="icon">📄</span> Upload Notes
                </a>
                <a href="manage_notes.php" class="nav-item">
                    <span class="icon">📋</span> Manage Notes
                </a>
                <a href="manage_students.php" class="nav-item">
                    <span class="icon">👥</span> Manage Students
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="admin-info">
                    <p><?= sanitize($admin['username']) ?></p>
                    <small>Administrator</small>
                </div>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Upload Notes</h1>
                <p>Add new study materials</p>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p><?= $success ?></p>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="subject">Subject Name *</label>
                        <input type="text" id="subject" name="subject" required placeholder="e.g., Data Structures" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required placeholder="Brief description of the notes" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="department">Department *</label>
                            <select id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="CSE" <?= ($_POST['department'] ?? '') === 'CSE' ? 'selected' : '' ?>>Computer Science (CSE)</option>
                                <option value="ECE" <?= ($_POST['department'] ?? '') === 'ECE' ? 'selected' : '' ?>>Electronics (ECE)</option>
                                <option value="ME" <?= ($_POST['department'] ?? '') === 'ME' ? 'selected' : '' ?>>Mechanical (ME)</option>
                                <option value="CE" <?= ($_POST['department'] ?? '') === 'CE' ? 'selected' : '' ?>>Civil (CE)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="semester">Semester *</label>
                            <select id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <option value="1" <?= ($_POST['semester'] ?? '') === '1' ? 'selected' : '' ?>>1st Semester</option>
                                <option value="2" <?= ($_POST['semester'] ?? '') === '2' ? 'selected' : '' ?>>2nd Semester</option>
                                <option value="3" <?= ($_POST['semester'] ?? '') === '3' ? 'selected' : '' ?>>3rd Semester</option>
                                <option value="4" <?= ($_POST['semester'] ?? '') === '4' ? 'selected' : '' ?>>4th Semester</option>
                                <option value="5" <?= ($_POST['semester'] ?? '') === '5' ? 'selected' : '' ?>>5th Semester</option>
                                <option value="6" <?= ($_POST['semester'] ?? '') === '6' ? 'selected' : '' ?>>6th Semester</option>
                                <option value="7" <?= ($_POST['semester'] ?? '') === '7' ? 'selected' : '' ?>>7th Semester</option>
                                <option value="8" <?= ($_POST['semester'] ?? '') === '8' ? 'selected' : '' ?>>8th Semester</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="file">Upload File (PDF or DOC) *</label>
                        <input type="file" id="file" name="file" required accept=".pdf,.doc,.docx">
                        <small>Max file size: 10MB. Allowed formats: PDF, DOC, DOCX</small>
                    </div>

                    <button type="submit" class="btn-primary">Upload Note</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
