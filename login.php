<?php
session_start();
require_once 'config/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        if ($remember) {
            setcookie('rememberme', $user['id'], time() + (86400 * 30), "/");
        }
        header('Location: feed.php');
        exit;
    } else {
        $errors[] = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            min-height: 100vh;
        }
        .login-card {
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(60,72,88,0.12);
            border: none;
            background: #fff;
            padding: 2.5rem 2rem;
        }
        .login-title {
            font-weight: bold;
            color: #6a82fb;
            letter-spacing: 1px;
        }
        .form-control {
            border-radius: 1rem;
            background: #f8fafc;
        }
        .btn-login {
            background: linear-gradient(90deg, #6a82fb 0%, #fc5c7d 100%);
            color: #fff;
            font-weight: bold;
            border-radius: 2rem;
            box-shadow: 0 2px 8px rgba(60,72,88,0.10);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            background: linear-gradient(90deg, #fc5c7d 0%, #6a82fb 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(60,72,88,0.16);
        }
        .register-link {
            color: #fc5c7d;
            font-weight: 500;
            text-decoration: none;
        }
        .register-link:hover {
            color: #6a82fb;
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
    <div class="col-12 col-sm-8 col-md-6 col-lg-4">
        <div class="login-card">
            <h2 class="mb-4 text-center login-title"><i class="fas fa-sign-in-alt me-2"></i>Login</h2>
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3 input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3 input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-login w-100 mb-2">Login</button>
            </form>
            <div class="mt-3 text-center">
                Don't have an account? <a href="register.php" class="register-link">Register</a>
            </div>
        </div>
    </div>
</div>
</body>
</html> 