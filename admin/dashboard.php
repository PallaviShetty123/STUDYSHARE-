<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();

// Get statistics
$stats = [
    'total_students' => $pdo->query('SELECT COUNT(*) as count FROM students')->fetch()['count'],
    'total_notes' => $pdo->query('SELECT COUNT(*) as count FROM notes')->fetch()['count'],
    'total_likes' => $pdo->query('SELECT COUNT(*) as count FROM likes')->fetch()['count'],
];

// Get recent notes
$recent_notes = $pdo->query('SELECT * FROM notes ORDER BY upload_date DESC LIMIT 10')->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | StudyShare</title>
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
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="upload_notes.php" class="nav-item">
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
                <h1>Dashboard</h1>
                <p>Welcome back, <?= sanitize($admin['username']) ?>!</p>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3>Total Students</h3>
                        <p class="stat-value"><?= $stats['total_students'] ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-info">
                        <h3>Total Notes</h3>
                        <p class="stat-value"><?= $stats['total_notes'] ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">❤️</div>
                    <div class="stat-info">
                        <h3>Total Likes</h3>
                        <p class="stat-value"><?= $stats['total_likes'] ?></p>
                    </div>
                </div>
            </div>

            <section class="recent-notes-section">
                <h2>Recent Notes Uploaded</h2>
                <table class="notes-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Upload Date</th>
                            <th>Likes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_notes): ?>
                            <?php foreach ($recent_notes as $note): ?>
                                <tr>
                                    <td><?= sanitize($note['subject']) ?></td>
                                    <td><?= sanitize($note['department']) ?></td>
                                    <td><?= $note['semester'] ?></td>
                                    <td><?= date('M d, Y', strtotime($note['upload_date'])) ?></td>
                                    <td><?= $note['likes'] ?></td>
                                    <td>
                                        <a href="edit_note.php?id=<?= $note['id'] ?>" class="btn-small">Edit</a>
                                        <a href="delete_note.php?id=<?= $note['id'] ?>" class="btn-small btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No notes uploaded yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
