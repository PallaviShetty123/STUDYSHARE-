<?php
session_start();
require_once __DIR__ . '/../common/auth_lecturer.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$lecturer = getCurrentLecturer();
$pdo = db();

$note_id = intval($_GET['note_id'] ?? 0);

if ($note_id <= 0) {
    redirect('dashboard.php');
}

// Get note details
$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ? AND lecturer_id = ?');
$stmt->execute([$note_id, $lecturer['id']]);
$note = $stmt->fetch();

if (!$note) {
    redirect('dashboard.php');
}

// Delete the file
$file_path = __DIR__ . '/../' . $note['file_path'];
if (file_exists($file_path)) {
    unlink($file_path);
}

// Delete from database
$stmt = $pdo->prepare('DELETE FROM notes WHERE id = ?');
$stmt->execute([$note_id]);

// Also delete related likes
$stmt = $pdo->prepare('DELETE FROM likes WHERE note_id = ?');
$stmt->execute([$note_id]);

// Also delete related downloads
$stmt = $pdo->prepare('DELETE FROM downloads WHERE note_id = ?');
$stmt->execute([$note_id]);

redirect('dashboard.php?deleted=1');
?>
