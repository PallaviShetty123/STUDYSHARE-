<?php
session_start();
require_once __DIR__ . '/../common/auth_lecturer.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$lecturer = getCurrentLecturer();
$pdo = db();
$errors = [];
$success = '';

$columnExists = false;
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM lecturers LIKE 'profile_image'");
    $columnExists = (bool) $stmt->fetch();
} catch (Exception $e) {
    $columnExists = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['profile_image']['name'])) {
    if (!$columnExists) {
        $errors[] = 'Profile image upload is not enabled. Please add the lecturers.profile_image field in your database.';
    } else {
        $file = $_FILES['profile_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'Only JPG and PNG images are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image size must be less than 5MB.';
        } else {
            if (!file_exists(PROFILE_DIR)) {
                mkdir(PROFILE_DIR, 0755, true);
            }

            if (!empty($lecturer['profile_image']) && file_exists(PROFILE_DIR . $lecturer['profile_image'])) {
                unlink(PROFILE_DIR . $lecturer['profile_image']);
            }

            $filename = uniqid() . '_' . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], PROFILE_DIR . $filename)) {
                $stmt = $pdo->prepare('UPDATE lecturers SET profile_image = ? WHERE id = ?');
                if ($stmt->execute([$filename, $lecturer['id']])) {
                    $success = 'Profile picture updated successfully!';
                    $lecturer['profile_image'] = $filename;
                } else {
                    $errors[] = 'Failed to save image to database.';
                    @unlink(PROFILE_DIR . $filename);
                }
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Profile | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
    <div class="student-layout">
        <aside class="student-sidebar">
            <div class="sidebar-brand">
                <h2>StudyShare</h2>
                <p>Lecturer Portal</p>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="profile.php" class="nav-item active">
                    <span class="icon">👤</span> Profile
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="student-info">
                    <p><?= sanitize($lecturer['name']) ?></p>
                    <small><?= sanitize($lecturer['department']) ?></small>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </aside>

        <main class="student-content">
            <header class="student-header">
                <div>
                    <h1>My Profile</h1>
                    <p>Update your display picture and view lecturer details.</p>
                </div>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p><?= $success ?></p>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?= sanitize($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section class="profile-container">
                <div class="profile-picture-section">
                    <div class="profile-picture">
                        <?php if (!empty($lecturer['profile_image']) && file_exists(PROFILE_DIR . $lecturer['profile_image'])): ?>
                            <img src="<?= getProfileImage($lecturer['profile_image']) ?>" alt="Profile Picture">
                        <?php else: ?>
                            <div class="default-avatar">👤</div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="upload-picture-form">
                        <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/jpg" required>
                        <label for="profile_image" class="upload-label">Choose Picture</label>
                        <button type="submit" class="btn-primary">Upload</button>
                    </form>
                    <p class="help-text">JPG or PNG, max 5MB.</p>
                </div>

                <div class="profile-details-section">
                    <h2>Lecturer Information</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <label>Full Name</label>
                            <p><?= sanitize($lecturer['name']) ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Email</label>
                            <p><?= sanitize($lecturer['email'] ?? 'Not set') ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Department</label>
                            <p><?= sanitize($lecturer['department']) ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Assigned Subject</label>
                            <p><?= sanitize($lecturer['subject'] ?? 'Not assigned') ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Member Since</label>
                            <p><?= date('M d, Y', strtotime($lecturer['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
