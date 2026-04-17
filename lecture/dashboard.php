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
        $upload_error = 'Unable to determine your assigned subject. Please contact admin.';
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
                        $filename,
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

// Get lecturer subject details by assigned subject name
$stmt = $pdo->prepare('SELECT * FROM subjects WHERE subject_name = ? LIMIT 1');
$stmt->execute([$lecturer['subject']]);
$lecturer_subject = $stmt->fetch();

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
            display: flex;
            min-height: 100vh;
            background-color: #f1f5f9;
        }

        .lecturer-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            width: 250px;
            min-height: 100vh;
            overflow-y: auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
            display: flex;
            flex-direction: column;
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
            margin-bottom: 1.5rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 12px;
            color: white;
            text-decoration: none;
            transition: all 0.25s ease;
            opacity: 0.95;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.12);
            opacity: 1;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.18);
            opacity: 1;
        }

        .nav-item .icon {
            margin-right: 12px;
            font-size: 18px;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
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
            min-height: 100vh;
        }

        .lecturer-header {
            margin-bottom: 32px;
        }

        .dashboard-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 36px;
        }

        .summary-card {
            background: white;
            padding: 24px;
            border-radius: 18px;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
        }

        .subject-card-wrapper {
            margin-bottom: 36px;
        }

        .subject-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #5566f2 0%, #8f9eff 45%, #c4d5ff 100%);
            border-radius: 28px;
            padding: 32px 32px;
            box-shadow: 0 24px 48px rgba(37, 99, 235, 0.18);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            animation: floatCard 8s ease-in-out infinite;
        }

        .subject-card::before,
        .subject-card::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .subject-card::before {
            right: -80px;
            top: -80px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.22);
            animation: drift 10s ease-in-out infinite;
        }

        .subject-card::after {
            left: -60px;
            bottom: -60px;
            width: 180px;
            height: 180px;
            background: rgba(107, 119, 255, 0.18);
            animation: driftReverse 12s ease-in-out infinite;
        }

        @keyframes floatCard {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @keyframes drift {
            0% { transform: translate(0, 0); opacity: 0.8; }
            50% { transform: translate(18px, 12px); opacity: 0.6; }
            100% { transform: translate(0, 0); opacity: 0.8; }
        }

        @keyframes driftReverse {
            0% { transform: translate(0, 0); opacity: 0.75; }
            50% { transform: translate(-14px, -20px); opacity: 0.55; }
            100% { transform: translate(0, 0); opacity: 0.75; }
        }

        .subject-card-content {
            position: relative;
            z-index: 1;
            max-width: 70%;
        }

        .subject-card-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.12);
            color: #4f46e5;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .subject-card-title {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }

        .subject-card-meta {
            color: #475569;
            font-size: 0.95rem;
        }

        .ppt-badge {
            position: relative;
            z-index: 1;
            min-width: 140px;
            padding: 18px 22px;
            border-radius: 22px;
            background: linear-gradient(135deg, #ff9a8b 0%, #ff6b6b 100%);
            color: white;
            font-weight: 700;
            text-align: center;
            box-shadow: 0 16px 30px rgba(255, 107, 107, 0.25);
        }

        .ppt-badge small {
            display: block;
            font-size: 0.8rem;
            opacity: 0.9;
            margin-top: 4px;
        }

        .summary-card h3 {
            margin-bottom: 1rem;
            font-size: 1rem;
            color: #475569;
        }

        .summary-card .summary-value {
            font-size: 2.1rem;
            color: #1f2937;
            font-weight: 700;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 30px;
            align-items: flex-start;
        }

        .dashboard-grid.full-width {
            grid-template-columns: 1fr;
        }

        .card-panel {
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
        }

        .upload-section,
        .notes-section {
            background: transparent;
            box-shadow: none;
            padding: 0;
        }

        .upload-section h2,
        .notes-section h2 {
            margin-bottom: 18px;
        }

        .btn-delete {
            padding: 8px 14px;
            font-size: 13px;
        }

        @media (max-width: 1024px) {
            .lecturer-main {
                margin-left: 0;
                padding: 24px;
            }

            .dashboard-summary {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .lecturer-sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .sidebar-footer {
                position: static;
            }
        }

        .lecturer-header h1 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 5px;
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
                <a href="profile.php" class="nav-item">
                    <span class="icon">👤</span> Profile
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
                <p>Upload and manage your course materials from one dashboard.</p>
            </div>

            <div class="dashboard-summary">
                <div class="summary-card">
                    <h3>Assigned Subject</h3>
                    <div class="summary-value"><?= sanitize($lecturer_subject['subject_name'] ?? $lecturer['subject'] ?? 'Not assigned') ?></div>
                </div>
                <div class="summary-card">
                    <h3>Total Notes Uploaded</h3>
                    <div class="summary-value"><?= count($lecturer_notes) ?></div>
                </div>
                <div class="summary-card">
                    <h3>Latest Upload</h3>
                    <div class="summary-value">
                        <?php if (!empty($lecturer_notes)): ?>
                            <?= date('M d, Y', strtotime($lecturer_notes[0]['upload_date'])) ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="subject-card-wrapper">
                <div class="subject-card">
                    <div class="subject-card-content">
                        <div class="subject-card-label">Assigned Subject</div>
                        <h2 class="subject-card-title"><?= sanitize($lecturer_subject['subject_name'] ?? $lecturer['subject']) ?></h2>
                        <p class="subject-card-meta">
                            <?= sanitize($lecturer_subject['subject_code'] ?? '') ?> • <?= sanitize($lecturer['department']) ?>
                        </p>
                    </div>
                    <div class="subject-card-meta" style="text-align:right; font-size:0.95rem; color:#eef2ff;">
                        <strong>Static subject</strong>
                        <br>
                        <span style="opacity:0.85;">Managed by admin</span>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="card-panel upload-section">
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
                        <input type="hidden" name="subject_id" value="<?= intval($lecturer_subject['id'] ?? 0) ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" value="<?= sanitize($lecturer_subject['subject_name'] ?? $lecturer['subject']) ?>" readonly>
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

                <div class="card-panel notes-section">
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
            </div>
        </main>
    </div>
</body>
</html>
