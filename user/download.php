<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$student = getCurrentStudent();
$note_id = intval($_GET['id'] ?? 0);

if ($note_id === 0) {
    redirect('notes.php');
}

$pdo = db();

// Get the note
$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ? AND department = ? AND semester = ?');
$stmt->execute([$note_id, $student['department'], $student['semester']]);
$note = $stmt->fetch();

if (!$note) {
    redirect('notes.php');
}

$file_path = NOTES_DIR . $note['file_path'];

if (!file_exists($file_path)) {
    redirect('notes.php');
}

// Record download (optional)
$stmt = $pdo->prepare('INSERT INTO downloads (roll_no, note_id) VALUES (?, ?)');
$stmt->execute([$student['roll_no'], $note_id]);

// Download file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($note['file_path']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Pragma: no-cache');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');

readfile($file_path);
exit;
