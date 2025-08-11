<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center min-vh-100">
        <div class="card p-4 shadow-lg" style="max-width: 400px; width: 100%;">
            <h1 class="mb-4 text-center">Welcome!</h1>
            <p class="text-center">Join our community to share moments, connect, and explore.</p>
            <div class="d-grid gap-2 mt-4">
                <a href="login.php" class="btn btn-primary">Login</a>
                <a href="register.php" class="btn btn-outline-primary">Register</a>
            </div>
        </div>
    </div>
</body>
</html> 