<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$student = getCurrentStudent();
$pdo = db();

// Get unique subjects for this student's department and semester
$stmt = $pdo->prepare('SELECT DISTINCT subject FROM notes WHERE department = ? AND semester = ? ORDER BY subject');
$stmt->execute([$student['department'], $student['semester']]);
$subjects = $stmt->fetchAll();

// Get recent notes
$stmt = $pdo->prepare('SELECT * FROM notes WHERE department = ? AND semester = ? ORDER BY upload_date DESC LIMIT 6');
$stmt->execute([$student['department'], $student['semester']]);
$recent_notes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="student-layout">
        <aside class="student-sidebar">
            <div class="sidebar-brand">
                <h2>StudyShare</h2>
                <p>Student Portal</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="notes.php" class="nav-item">
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
                    <h1>Welcome, <?= sanitize($student['name']) ?></h1>
                    <p>Your study materials dashboard</p>
                </div>
            </header>

            <section class="student-info-section">
                <div class="info-card">
                    <div class="info-item">
                        <label>Roll Number:</label>
                        <span><?= sanitize($student['roll_no']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Department:</label>
                        <span><?= sanitize($student['department']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Semester:</label>
                        <span><?= $student['semester'] ?></span>
                    </div>
                </div>
            </section>

            <section class="subjects-section">
                <h2>Available Subjects</h2>
                <?php if ($subjects): ?>
                    <div class="subjects-grid">
                        <?php foreach ($subjects as $subject): ?>
                            <a href="notes.php?subject=<?= urlencode($subject['subject']) ?>" class="subject-card">
                                <div class="subject-icon">📖</div>
                                <div class="subject-name"><?= sanitize($subject['subject']) ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">No subjects available for your department and semester yet.</p>
                <?php endif; ?>
            </section>

            <section class="recent-notes-section">
                <h2>Recent Notes</h2>
                <?php if ($recent_notes): ?>
                    <div class="notes-grid">
                        <?php foreach ($recent_notes as $note): 
                            // Check if student liked this note
                            $stmt = $pdo->prepare('SELECT id FROM likes WHERE roll_no = ? AND note_id = ?');
                            $stmt->execute([$student['roll_no'], $note['id']]);
                            $liked = $stmt->fetch() !== false;
                        ?>
                            <div class="note-card">
                                <div class="note-header">
                                    <h3><?= sanitize($note['subject']) ?></h3>
                                    <small><?= date('M d, Y', strtotime($note['upload_date'])) ?></small>
                                </div>
                                <p class="note-description"><?= sanitize(substr($note['description'], 0, 100)) ?>...</p>
                                <div class="note-actions">
                                    <button class="like-btn <?= $liked ? 'liked' : '' ?>" data-note-id="<?= $note['id'] ?>" onclick="toggleLike(this, <?= $note['id'] ?>)">
                                        <span class="heart">❤️</span> <span class="like-count"><?= $note['likes'] ?></span>
                                    </button>
                                    <a href="download.php?id=<?= $note['id'] ?>" class="download-btn">⬇️ Download</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">No notes available yet.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
}
if (!empty($_GET['class'])) {
    $where .= ' AND class = ?';
    $params[] = sanitize($_GET['class']);
    $filters['class'] = sanitize($_GET['class']);
}
if (!empty($_GET['semester'])) {
</div>
                        <?php else: ?>
                            <p class="no-data">No notes available yet.</p>
                        <?php endif; ?>
                    </section>
                </main>
            </div>

            <script src="../assets/js/script.js"></script>
        </body>
        </html>
