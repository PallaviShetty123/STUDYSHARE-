<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/subjects.php';

// Insert student data if not already in database
insertStudentsData();

$student = getCurrentStudent();
$pdo = db();

// Get all subjects for this student
$student_subjects = getStudentSubjects($student['roll_no']);

// Calculate notes count for each subject
$subject_notes_count = [];
foreach ($student_subjects as $subject) {
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM notes WHERE subject_id = ?');
    $stmt->execute([$subject['id']]);
    $count = $stmt->fetch()['count'];
    $subject_notes_count[$subject['id']] = $count;
}

/**
 * Helper function to adjust color brightness
 */
function adjustBrightness($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    $rgb = array_map('hexdec', str_split($hex, 2));
    
    foreach ($rgb as &$value) {
        $value = max(0, min(255, $value + ($value * $percent / 100)));
    }
    
    return '#' . implode('', array_map(function($v) { return str_pad(dechex($v), 2, '0', STR_PAD_LEFT); }, $rgb));
}

/**
 * Helper function to show time ago
 */
function time_ago($timestamp) {
    if (is_string($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    
    $time_ago = time() - $timestamp;
    
    if ($time_ago < 60) {
        return "just now";
    } elseif ($time_ago < 3600) {
        $mins = floor($time_ago / 60);
        return $mins . " minute" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($time_ago < 86400) {
        $hours = floor($time_ago / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } else {
        $days = floor($time_ago / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/subjects.css">
</head>
<body>
    <div class="student-layout">
        <!-- Sidebar -->
        <aside class="student-sidebar">
            <div class="sidebar-brand">
                <h2>📚 StudyShare</h2>
                <p>Student Portal</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="notes.php" class="nav-item">
                    <span class="icon">📝</span> Browse Notes
                </a>
                <a href="profile.php" class="nav-item">
                    <span class="icon">👤</span> Profile
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="student-info">
                    <p><strong><?= sanitize($student['name']) ?></strong></p>
                    <p class="student-roll"><?= sanitize($student['roll_no']) ?></p>
                </div>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </aside>


        <!-- Main Content -->
        <main class="student-main">
            <!-- Header -->
            <div class="student-header">
                <h1>Welcome, <?= sanitize($student['name']) ?>! 👋</h1>
                <p>Your learning journey starts here</p>
            </div>

            <!-- Quick Stats -->
            <div class="stats-container">
                <div class="stat-card">
                    <span class="stat-icon">📚</span>
                    <div class="stat-content">
                        <p class="stat-label">Subjects</p>
                        <p class="stat-value"><?= count($student_subjects) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">📄</span>
                    <div class="stat-content">
                        <p class="stat-label">Available Notes</p>
                        <p class="stat-value"><?= array_sum($subject_notes_count) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">🌐</span>
                    <div class="stat-content">
                        <p class="stat-label">Language</p>
                        <p class="stat-value"><?= isHindiStudent($student['roll_no']) ? 'Hindi' : 'Kannada' ?></p>
                    </div>
                </div>
            </div>

            <!-- Subjects Section -->
            <section class="subjects-section">
                <h2 class="section-title">📖 Your Subjects</h2>
                <p class="section-subtitle">Click on any subject to view notes and materials</p>
                
                <div class="subjects-container">
                    <?php foreach ($student_subjects as $subject): ?>
                        <a href="subject-notes.php?subject_id=<?= $subject['id'] ?>" 
                           class="subject-card subject-row-card"
                           style="background: linear-gradient(135deg, <?= $subject['color_code'] ?> 0%, <?= adjustBrightness($subject['color_code'], -20) ?> 100%);">
                            <div class="subject-card-icon">📘</div>
                            <div class="subject-card-content">
                                <div class="subject-card-title"><?= sanitize($subject['subject_name']) ?></div>
                                <div class="subject-card-notes">
                                    <?= $subject_notes_count[$subject['id']] ?? 0 ?> notes available
                                </div>
                                <div class="subject-card-meta">
                                    <span><?= sanitize($subject['subject_code'] ?? 'CODE') ?></span>
                                    <span><?= isHindiStudent($student['roll_no']) ? 'Language: ' . getStudentLanguage($student['roll_no']) : 'Language: ' . getStudentLanguage($student['roll_no']) ?></span>
                                </div>
                            </div>
                            <div class="subject-card-actions">
                                <span class="subject-pill">Year <?= sanitize($student['semester']) ?> • Semester <?= sanitize($student['semester']) ?></span>
                                <span class="subject-pill">Department: <?= sanitize($student['department']) ?></span>
                                <div class="subject-card-bottom">
                                    <button type="button" class="subject-action-btn primary">Attendance</button>
                                    <button type="button" class="subject-action-btn secondary">Marks</button>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Recent Notes Section -->
            <section class="recent-notes-section">
                <h2 class="section-title">🔥 Recently Uploaded</h2>
                <div class="recent-notes-list">
                    <?php
                    $subject_ids = array_column($student_subjects, 'id');
                    if (!empty($subject_ids)) {
                        $placeholders = implode(',', array_fill(0, count($subject_ids), '?'));
                        $stmt = $pdo->prepare("
                            SELECT n.*, s.subject_name, s.color_code, l.name as lecturer_name 
                            FROM notes n 
                            LEFT JOIN subjects s ON n.subject_id = s.id 
                            LEFT JOIN lecturers l ON n.lecturer_id = l.id 
                            WHERE n.subject_id IN ($placeholders) 
                            ORDER BY n.upload_date DESC 
                            LIMIT 5
                        ");
                        $stmt->execute($subject_ids);
                        $recent_notes = $stmt->fetchAll();
                        
                        if ($recent_notes) {
                            foreach ($recent_notes as $note) {
                                ?>
                                <div class="recent-note-card">
                                    <div class="note-color-bar" style="background-color: <?= $note['color_code'] ?>"></div>
                                    <div class="note-content">
                                        <h4><?= sanitize($note['subject_name']) ?></h4>
                                        <p class="note-title-text"><?= sanitize($note['description']) ?></p>
                                        <p class="note-meta">Uploaded by <?= $note['lecturer_name'] ?? 'Admin' ?> • <?= time_ago($note['upload_date']) ?></p>
                                    </div>
                                    <div class="note-actions">
                                        <a href="download.php?note_id=<?= $note['id'] ?>" class="btn-small btn-download">Download</a>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="no-data">No notes available yet. Check back soon!</p>';
                        }
                    }
                    ?>
                </div>
            </section>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
