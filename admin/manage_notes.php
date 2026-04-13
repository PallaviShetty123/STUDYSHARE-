<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();

// Get all notes
$notes = $pdo->query('SELECT * FROM notes ORDER BY upload_date DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notes | StudyShare Admin</title>
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
                <h1>Manage Notes</h1>
                <p>View, edit, and delete uploaded notes</p>
            </header>

            <table class="notes-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Description</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Upload Date</th>
                        <th>Likes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($notes): ?>
                        <?php foreach ($notes as $note): ?>
                            <tr>
                                <td><?= sanitize($note['subject']) ?></td>
                                <td><?= sanitize(substr($note['description'], 0, 50)) ?>...</td>
                                <td><?= sanitize($note['department']) ?></td>
                                <td><?= $note['semester'] ?></td>
                                <td><?= date('M d, Y', strtotime($note['upload_date'])) ?></td>
                                <td><?= $note['likes'] ?></td>
                                <td>
                                    <a href="edit_note.php?id=<?= $note['id'] ?>" class="btn-small">Edit</a>
                                    <a href="delete_note.php?id=<?= $note['id'] ?>" class="btn-small btn-danger" onclick="return confirm('Delete this note? This action cannot be undone.')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No notes found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
