<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$note_id = intval($_GET['id'] ?? 0);

if ($note_id === 0) {
    redirect('manage_notes.php');
}

$pdo = db();

// Get the note to delete the file
$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
$stmt->execute([$note_id]);
$note = $stmt->fetch();

if ($note) {
    // Delete the file
    if (file_exists(NOTES_DIR . $note['file_path'])) {
        unlink(NOTES_DIR . $note['file_path']);
    }
    
    // Delete from database
    $stmt = $pdo->prepare('DELETE FROM notes WHERE id = ?');
    $stmt->execute([$note_id]);
}

redirect('manage_notes.php');
