<?php
session_start();
require_once 'config/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $bio = trim($_POST['bio']);
    $profile_pic = null;

    // Validate
    if (!$username || !$email || !$password) {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    }
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Invalid image type.';
        } else {
            $profile_pic = 'uploads/profile_pics/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic);
        }
    }
    // If no errors, insert user
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, bio, profile_pic) VALUES (?, ?, ?, ?, ?)');
        try {
            $stmt->execute([$username, $email, $hash, $bio, $profile_pic]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: feed.php');
            exit;
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'users.username')) {
                $errors[] = 'Username already taken.';
            } elseif (str_contains($e->getMessage(), 'users.email')) {
                $errors[] = 'Email already registered.';
            } else {
                $errors[] = 'Registration failed.';
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
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            min-height: 100vh;
        }
        .register-card {
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(60,72,88,0.12);
            border: none;
            background: #fff;
            padding: 2.5rem 2rem;
        }
        .register-title {
            font-weight: bold;
            color: #fc5c7d;
            letter-spacing: 1px;
        }
        .form-control {
            border-radius: 1rem;
            background: #f8fafc;
        }
        .btn-register {
            background: linear-gradient(90deg, #fc5c7d 0%, #6a82fb 100%);
            color: #fff;
            font-weight: bold;
            border-radius: 2rem;
            box-shadow: 0 2px 8px rgba(60,72,88,0.10);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn-register:hover {
            background: linear-gradient(90deg, #6a82fb 0%, #fc5c7d 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(60,72,88,0.16);
        }
        .login-link {
            color: #6a82fb;
            font-weight: 500;
            text-decoration: none;
        }
        .login-link:hover {
            color: #fc5c7d;
            text-decoration: underline;
        }
        .input-group-text {
            background: #f8fafc;
            border-radius: 1rem 0 0 1rem;
            border: 1px solid #d1d9e6;
        }
    </style>
</head>
<body>
<div class="container d-flex flex-column justify-content-center align-items-center min-vh-100">
    <div class="col-12 col-sm-8 col-md-6 col-lg-5">
        <div class="register-card">
            <h2 class="mb-4 text-center register-title"><i class="fas fa-user-plus me-2"></i>Register</h2>
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                </div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3 input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3 input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3 input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="mb-3 input-group">
                    <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                    <textarea name="bio" class="form-control" placeholder="Bio"></textarea>
                </div>
                <div class="mb-3 input-group">
                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                    <input type="file" name="profile_pic" class="form-control">
                </div>
                <button type="submit" class="btn btn-register w-100 mb-2">Register</button>
            </form>
            <div class="mt-3 text-center">
                Already have an account? <a href="login.php" class="login-link">Login</a>
            </div>
        </div>
    </div>
</div>
</body>
</html> 