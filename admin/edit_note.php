<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();
$note_id = intval($_GET['id'] ?? 0);

if ($note_id === 0) {
    redirect('manage_notes.php');
}

$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
$stmt->execute([$note_id]);
$note = $stmt->fetch();

if (!$note) {
    redirect('manage_notes.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $semester = intval($_POST['semester'] ?? 0);
    
    if (empty($subject) || empty($description) || empty($department) || $semester === 0) {
        $errors[] = 'Please fill all required fields.';
    } else {
        $file_path = $note['file_path'];
        
        // If new file uploaded
        if (!empty($_FILES['file']['name'])) {
            if (!isValidFileUpload($_FILES['file']['name'])) {
                $errors[] = 'Invalid file type. Only PDF and DOC files are allowed.';
            } elseif ($_FILES['file']['size'] > MAX_FILE_SIZE) {
                $errors[] = 'File size exceeds 10MB limit.';
            } else {
                $new_filename = uploadFile($_FILES['file'], NOTES_DIR);
                if ($new_filename) {
                    // Delete old file
                    if (file_exists(NOTES_DIR . $file_path)) {
                        unlink(NOTES_DIR . $file_path);
                    }
                    $file_path = $new_filename;
                } else {
                    $errors[] = 'Failed to upload new file.';
                }
            }
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare('UPDATE notes SET subject = ?, description = ?, file_path = ?, department = ?, semester = ? WHERE id = ?');
            if ($stmt->execute([$subject, $description, $file_path, $department, $semester, $note_id])) {
                $success = 'Note updated successfully!';
                // Refresh note data
                $stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
                $stmt->execute([$note_id]);
                $note = $stmt->fetch();
            } else {
                $errors[] = 'Failed to update note.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note | StudyShare Admin</title>
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
                <a href="upload_notes.php" class="nav-item">
                    <span class="icon">📄</span> Upload Notes
                </a>
                <a href="manage_notes.php" class="nav-item active">
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
                <h1>Edit Note</h1>
                <a href="manage_notes.php" class="btn-secondary">← Back</a>
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
                        <input type="text" id="subject" name="subject" required value="<?= sanitize($note['subject']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required rows="4"><?= sanitize($note['description']) ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="department">Department *</label>
                            <select id="department" name="department" required>
                                <option value="CSE" <?= $note['department'] === 'CSE' ? 'selected' : '' ?>>Computer Science (CSE)</option>
                                <option value="ECE" <?= $note['department'] === 'ECE' ? 'selected' : '' ?>>Electronics (ECE)</option>
                                <option value="ME" <?= $note['department'] === 'ME' ? 'selected' : '' ?>>Mechanical (ME)</option>
                                <option value="CE" <?= $note['department'] === 'CE' ? 'selected' : '' ?>>Civil (CE)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="semester">Semester *</label>
                            <select id="semester" name="semester" required>
                                <option value="1" <?= $note['semester'] === 1 ? 'selected' : '' ?>>1st Semester</option>
                                <option value="2" <?= $note['semester'] === 2 ? 'selected' : '' ?>>2nd Semester</option>
                                <option value="3" <?= $note['semester'] === 3 ? 'selected' : '' ?>>3rd Semester</option>
                                <option value="4" <?= $note['semester'] === 4 ? 'selected' : '' ?>>4th Semester</option>
                                <option value="5" <?= $note['semester'] === 5 ? 'selected' : '' ?>>5th Semester</option>
                                <option value="6" <?= $note['semester'] === 6 ? 'selected' : '' ?>>6th Semester</option>
                                <option value="7" <?= $note['semester'] === 7 ? 'selected' : '' ?>>7th Semester</option>
                                <option value="8" <?= $note['semester'] === 8 ? 'selected' : '' ?>>8th Semester</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="file">Replace File (PDF or DOC) - Optional</label>
                        <input type="file" id="file" name="file" accept=".pdf,.doc,.docx">
                        <small>Current file: <?= sanitize($note['file_path']) ?></small>
                    </div>

                    <button type="submit" class="btn-primary">Update Note</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
