<?php
require_once __DIR__ . '/common/functions.php';

// Redirect if already logged in
if (isStudentLoggedIn()) {
    redirect('user/dashboard.php');
}
if (isAdminLoggedIn()) {
    redirect('admin/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyShare - Smart Study Material Sharing Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .landing-container {
            max-width: 600px;
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .landing-container h1 {
            font-size: 2.5rem;
            color: #1f2937;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .landing-container p {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .features {
            background: #f9fafb;
            padding: 2rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .features h2 {
            font-size: 1.25rem;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .feature-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.75rem;
            color: #4b5563;
        }

        .feature-item:before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #6366f1;
            color: white;
        }

        .btn-primary:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-secondary:hover {
            background: #d1d5db;
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            .landing-container {
                padding: 1.5rem;
            }

            .landing-container h1 {
                font-size: 1.75rem;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <h1>📚 StudyShare</h1>
        <p>Smart Study Material Sharing Platform</p>

        <div class="features">
            <h2>Features</h2>
            <div class="feature-item">Access study materials by department & semester</div>
            <div class="feature-item">Like and download notes instantly</div>
            <div class="feature-item">Upload and manage study resources</div>
            <div class="feature-item">Secure roll number based authentication</div>
        </div>

        <div class="button-group">
            <a href="student/" class="btn btn-primary">Student Portal</a>
            <a href="lectures/" class="btn btn-secondary">Lecturer Portal</a>
            <a href="admin/" class="btn btn-secondary">Admin Portal</a>
        </div>
    </div>
</body>
</html>
