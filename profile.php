<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) && !isset($_COOKIE['rememberme'])) {
    header('Location: login.php');
    exit;
}
$current_user_id = $_SESSION['user_id'] ?? $_COOKIE['rememberme'];
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $current_user_id;

// Update profile
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $profile_id == $current_user_id) {
    $bio = trim($_POST['bio']);
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $profile_pic = 'uploads/profile_pics/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic);
        } else {
            $errors[] = 'Invalid image type.';
        }
    }
    $sql = 'UPDATE users SET bio = ?';
    $params = [$bio];
    if ($profile_pic) {
        $sql .= ', profile_pic = ?';
        $params[] = $profile_pic;
    }
    $sql .= ' WHERE id = ?';
    $params[] = $current_user_id;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}
// Fetch user
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$profile_id]);
$user = $stmt->fetch();
if (!$user) {
    echo 'User not found.';
    exit;
}
// Fetch user's posts
$pstmt = $pdo->prepare('SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC');
$pstmt->execute([$profile_id]);
$posts = $pstmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="profile-banner"></div>
            <div class="text-center">
                <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'uploads/profile_pics/default.png'; ?>" class="profile-avatar mb-2">
                <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($user['username']); ?></h3>
                <div class="profile-bio mb-3"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></div>
                <?php if ($profile_id == $current_user_id): ?>
                    <form method="post" enctype="multipart/form-data" class="mt-3 mb-4">
                        <div class="mb-2 input-group">
                            <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                            <textarea name="bio" class="form-control" placeholder="Update your bio..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                        </div>
                        <div class="mb-2 input-group">
                            <span class="input-group-text"><i class="fas fa-image"></i></span>
                            <input type="file" name="profile_pic" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger mt-2">
                            <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <h4 class="mb-3 mt-5"><i class="fas fa-images me-2"></i>Posts</h4>
            <?php foreach ($posts as $post): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <p class="fs-5 mb-2"><?php echo nl2br(htmlspecialchars($post['text'])); ?></p>
                        <?php if ($post['image']): ?>
                            <img src="<?php echo $post['image']; ?>" class="img-fluid rounded mb-2">
                        <?php endif; ?>
                        <div class="text-muted small"><i class="far fa-clock me-1"></i>Posted on <?php echo $post['created_at']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html> 