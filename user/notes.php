<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$student = getCurrentStudent();
$pdo = db();

// Get filter parameters
$subject_filter = sanitize($_GET['subject'] ?? '');

// Build query
if ($subject_filter) {
    $stmt = $pdo->prepare('SELECT * FROM notes WHERE department = ? AND semester = ? AND subject = ? ORDER BY upload_date DESC');
    $stmt->execute([$student['department'], $student['semester'], $subject_filter]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM notes WHERE department = ? AND semester = ? ORDER BY upload_date DESC');
    $stmt->execute([$student['department'], $student['semester']]);
}
$notes = $stmt->fetchAll();

// Get unique subjects
$stmt = $pdo->prepare('SELECT DISTINCT subject FROM notes WHERE department = ? AND semester = ? ORDER BY subject');
$stmt->execute([$student['department'], $student['semester']]);
$subjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Notes | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/notes.css">
</head>
<body>
    <div class="student-layout">
        <aside class="student-sidebar">
            <div class="sidebar-brand">
                <h2>StudyShare</h2>
                <p>Student Portal</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="notes.php" class="nav-item active">
                    <span class="icon">📚</span> Browse Notes
                </a>
                <a href="profile.php" class="nav-item">
                    <span class="icon">👤</span> Profile
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="student-info">
                    <p><?= sanitize($student['name']) ?></p>
                    <small><?= sanitize($student['roll_no']) ?></small>
                </div>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </aside>

        <main class="student-content">
            <header class="student-header">
                <div>
                    <h1>Browse Notes</h1>
                    <p>View and download study materials</p>
                </div>
            </header>

            <section class="filter-section">
                <h2>Filter by Subject</h2>
                <div class="subject-filters">
                    <a href="notes.php" class="filter-btn <?= empty($subject_filter) ? 'active' : '' ?>">All Subjects</a>
                    <?php foreach ($subjects as $subject): ?>
                        <a href="notes.php?subject=<?= urlencode($subject['subject']) ?>" class="filter-btn <?= $subject_filter === $subject['subject'] ? 'active' : '' ?>">
                            <?= sanitize($subject['subject']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="notes-list-section">
                <h2><?= $subject_filter ? sanitize($subject_filter) . ' Notes' : 'All Notes' ?> (<?= count($notes) ?>)</h2>
                
                <?php if ($notes): ?>
                    <div class="notes-list">
                        <?php foreach ($notes as $note): 
                            // Check if student liked this note
                            $stmt = $pdo->prepare('SELECT id FROM likes WHERE roll_no = ? AND note_id = ?');
                            $stmt->execute([$student['roll_no'], $note['id']]);
                            $liked = $stmt->fetch() !== false;
                        ?>
                            <div class="note-item">
                                <div class="note-content">
                                    <h3><?= sanitize($note['subject']) ?></h3>
                                    <p class="note-description"><?= sanitize($note['description']) ?></p>
                                    <div class="note-meta">
                                        <span class="meta-item">📅 <?= date('M d, Y', strtotime($note['upload_date'])) ?></span>
                                        <span class="meta-item">📂 <?= sanitize($note['department']) ?></span>
                                    </div>
                                </div>
                                <div class="note-actions">
                                    <button class="like-btn <?= $liked ? 'liked' : '' ?>" data-note-id="<?= $note['id'] ?>" onclick="toggleLike(this, <?= $note['id'] ?>)">
                                        <span class="heart">❤️</span>
                                        <span class="like-count"><?= $note['likes'] ?></span>
                                    </button>
                                    <a href="download.php?id=<?= $note['id'] ?>" class="download-btn">⬇️ Download</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">No notes available for this subject.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
