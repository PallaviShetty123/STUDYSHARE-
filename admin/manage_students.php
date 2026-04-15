<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();

$errors = [];
$success = '';

// Handle CSV import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['csv_file']['name'])) {
    if ($_FILES['csv_file']['type'] !== 'text/csv' && $_FILES['csv_file']['type'] !== 'application/vnd.ms-excel') {
        $errors[] = 'Please upload a valid CSV file.';
    } else {
        $file = $_FILES['csv_file']['tmp_name'];
        $row = 0;
        $imported = 0;
        
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if ($row === 1) continue; // Skip header
                
                if (count($data) >= 5) {
                    $roll_no = trim($data[0]);
                    $name = trim($data[1]);
                    $dob = trim($data[2]);
                    $department = trim($data[3]);
                    $semester = intval(trim($data[4]));

                    if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $dob, $matches)) {
                        $dob = sprintf('%s-%s-%s', $matches[3], $matches[2], $matches[1]);
                    }

                    if (!empty($roll_no) && !empty($name) && !empty($dob)) {
                        $stmt = $pdo->prepare('INSERT IGNORE INTO students (roll_no, name, dob, department, semester) VALUES (?, ?, ?, ?, ?)');
                        if ($stmt->execute([$roll_no, $name, $dob, $department, $semester])) {
                            $imported++;
                        }
                    }
                }
            }
            fclose($handle);
            $success = "Imported $imported students successfully!";
        }
    }
}

// Get all students
$students = $pdo->query('SELECT * FROM students ORDER BY roll_no')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | StudyShare Admin</title>
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
                <a href="manage_notes.php" class="nav-item">
                    <span class="icon">📋</span> Manage Notes
                </a>
                <a href="manage_students.php" class="nav-item active">
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
                <h1>Manage Students</h1>
                <p>Import and manage student accounts</p>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p><?= $success ?></p>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section class="import-section">
                <h2>Import Student Dataset</h2>
                <p>Upload a CSV file with student data. Format: roll_no, name, dob (DD-MM-YYYY or YYYY-MM-DD), department, semester</p>
                
                <form method="POST" enctype="multipart/form-data" class="import-form">
                    <div class="form-group">
                        <label for="csv_file">Select CSV File *</label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    <button type="submit" class="btn-primary">Import Students</button>
                </form>
            </section>

            <section class="students-section">
                <h2>All Students (<?= count($students) ?>)</h2>
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>DOB</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($students): ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= sanitize($student['roll_no']) ?></td>
                                    <td><?= sanitize($student['name']) ?></td>
                                    <td><?= sanitize($student['department']) ?></td>
                                    <td><?= $student['semester'] ?></td>
                                    <td><?= date('d-m-Y', strtotime($student['dob'])) ?></td>
                                    <td>
                                        <a href="delete_student.php?roll_no=<?= urlencode($student['roll_no']) ?>" class="btn-small btn-danger" onclick="return confirm('Delete this student? This action cannot be undone.')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No students found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
