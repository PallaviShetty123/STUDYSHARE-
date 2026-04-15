<?php
session_start();
require_once __DIR__ . '/../common/auth_lecturer.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$lecturer = getCurrentLecturer();
$pdo = db();

// Handle file upload
$upload_message = '';
$upload_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['note_file'])) {
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    
    if ($subject_id <= 0) {
        $upload_error = 'Please select a subject.';
    } elseif (empty($description)) {
        $upload_error = 'Please enter a description.';
    } elseif ($_FILES['note_file']['error'] !== UPLOAD_ERR_OK) {
        $upload_error = 'File upload failed. Please try again.';
    } else {
        $file = $_FILES['note_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($file_ext !== 'pdf') {
            $upload_error = 'Only PDF files are allowed.';
        } elseif ($file['size'] > 50 * 1024 * 1024) { // 50MB limit
            $upload_error = 'File size exceeds 50MB limit.';
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = __DIR__ . '/../uploads/notes/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $filename = md5($lecturer['id'] . time() . $file['name']) . '.pdf';
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Insert note into database
                try {
                    $stmt = $pdo->prepare('
                        INSERT INTO notes (subject_id, description, file_path, lecturer_id, upload_date, department, semester)
                        VALUES (?, ?, ?, ?, NOW(), ?, ?)
                    ');
                    $stmt->execute([
                        $subject_id,
                        $description,
                        'uploads/notes/' . $filename,
                        $lecturer['id'],
                        $lecturer['department'],
                        4  // Default semester
                    ]);
                    
                    $upload_message = 'Note uploaded successfully!';
                } catch (Exception $e) {
                    $upload_error = 'Database error: ' . $e->getMessage();
                    unlink($filepath);
                }
            } else {
                $upload_error = 'Failed to save file. Please try again.';
            }
        }
    }
}

// Get all subjects
$stmt = $pdo->prepare('SELECT * FROM subjects ORDER BY subject_name');
$stmt->execute();
$subjects = $stmt->fetchAll();

// Get lecturer's notes
$stmt = $pdo->prepare('
    SELECT n.*, s.subject_name, s.color_code 
    FROM notes n 
    LEFT JOIN subjects s ON n.subject_id = s.id 
    WHERE n.lecturer_id = ? 
    ORDER BY n.upload_date DESC
');
$stmt->execute([$lecturer['id']]);
$lecturer_notes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .lecturer-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .lecturer-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            overflow-y: auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            margin-bottom: 40px;
        }

        .sidebar-brand h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-brand p {
            font-size: 12px;
            opacity: 0.9;
        }

        .sidebar-nav {
            list-style: none;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            opacity: 0.9;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            opacity: 1;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            opacity: 1;
        }

        .nav-item .icon {
            margin-right: 12px;
            font-size: 18px;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }

        .lecturer-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .lecturer-info p {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .btn-logout {
            width: 100%;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .lecturer-main {
            margin-left: 250px;
            padding: 40px;
        }

        .lecturer-header {
            margin-bottom: 40px;
        }

        .lecturer-header h1 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 5px;
        }

        .upload-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .upload-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #fadbd8;
            color: #c0392b;
            border: 1px solid #e74c3c;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-upload {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.3s;
            font-size: 16px;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
        }

        .btn-upload:active {
            transform: translateY(0);
        }

        .notes-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .notes-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .notes-table {
            width: 100%;
            border-collapse: collapse;
        }

        .notes-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
        }

        .notes-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        .notes-table tr:hover {
            background-color: #f8f9fa;
        }

        .subject-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            color: white;
            font-size: 12px;
            font-weight: 600;
        }

        .note-date {
            color: #7f8c8d;
            font-size: 12px;
        }

        .btn-delete {
            padding: 6px 12px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        @media (max-width: 1024px) {
            .lecturer-layout {
                grid-template-columns: 1fr;
            }

            .lecturer-sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .lecturer-main {
                margin-left: 0;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .sidebar-footer {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="lecturer-layout">
        <!-- Sidebar -->
        <aside class="lecturer-sidebar">
            <div class="sidebar-brand">
                <h2>📚 StudyShare</h2>
                <p>Lecturer Portal</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon">📊</span> Dashboard
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="lecturer-info">
                    <p><strong><?= sanitize($lecturer['name']) ?></strong></p>
                    <p><?= sanitize($lecturer['department']) ?></p>
                </div>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="lecturer-main">
            <!-- Header -->
            <div class="lecturer-header">
                <h1>Welcome, <?= sanitize($lecturer['name']) ?>! 👋</h1>
                <p>Upload and manage your course materials</p>
            </div>

            <!-- Upload Section -->
            <div class="upload-section">
                <h2>📤 Upload New Note</h2>

                <?php if ($upload_message): ?>
                    <div class="alert alert-success">
                        <?= $upload_message ?>
                    </div>
                <?php endif; ?>

                <?php if ($upload_error): ?>
                    <div class="alert alert-error">
                        <?= $upload_error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="subject_id">Subject *</label>
                            <select id="subject_id" name="subject_id" required>
                                <option value="">Select a subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['id'] ?>">
                                        <?= sanitize($subject['subject_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="note_file">PDF File *</label>
                            <input type="file" id="note_file" name="note_file" accept=".pdf" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required placeholder="Enter note title or description..."></textarea>
                    </div>

                    <button type="submit" class="btn-upload">Upload Note</button>
                </form>
            </div>

            <!-- Notes List Section -->
            <div class="notes-section">
                <h2>📚 Your Uploaded Notes (<?= count($lecturer_notes) ?>)</h2>

                <?php if ($lecturer_notes): ?>
                    <table class="notes-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Description</th>
                                <th>Upload Date</th>
                                <th>Views</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lecturer_notes as $note): ?>
                                <tr>
                                    <td>
                                        <span class="subject-badge" style="background-color: <?= $note['color_code'] ?>">
                                            <?= sanitize($note['subject_name'] ?: 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td><?= sanitize(substr($note['description'], 0, 50)) ?></td>
                                    <td class="note-date"><?= date('M d, Y h:i A', strtotime($note['upload_date'])) ?></td>
                                    <td><?= $note['likes'] ?? 0 ?></td>
                                    <td>
                                        <a href="delete-note.php?note_id=<?= $note['id'] ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <p>📝 No notes uploaded yet. Start by uploading your first note!</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
