<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) && !isset($_COOKIE['rememberme'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'] ?? $_COOKIE['rememberme'];

// Create post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['id'])) {
    $text = trim($_POST['text']);
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $image = 'uploads/post_images/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $image);
        }
    }
    $stmt = $pdo->prepare('INSERT INTO posts (user_id, text, image) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $text, $image]);
    header('Location: feed.php');
    exit;
}
// View post and comments
if (isset($_GET['id'])) {
    $post_id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT posts.*, users.username, users.profile_pic FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?');
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    // Fetch comments
    $cstmt = $pdo->prepare('SELECT comments.*, users.username, users.profile_pic FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at ASC');
    $cstmt->execute([$post_id]);
    $comments = $cstmt->fetchAll();
    // Handle new comment
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
        $comment = trim($_POST['comment']);
        if ($comment) {
            $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)');
            $stmt->execute([$post_id, $user_id, $comment]);
            header('Location: post.php?id=' . $post_id);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post) ? 'Post' : 'Create Post'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <?php if (!isset($post)): ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4 shadow">
                    <h2 class="mb-4 text-center text-primary"><i class="fas fa-plus-circle me-2"></i>Create Post</h2>
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3 input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea name="text" class="form-control" placeholder="What's on your mind?" required></textarea>
                        </div>
                        <div class="mb-3 input-group">
                            <span class="input-group-text"><i class="fas fa-image"></i></span>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Post</button>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center bg-white border-0">
                        <img src="<?php echo $post['profile_pic'] ? $post['profile_pic'] : 'uploads/profile_pics/default.png'; ?>" class="rounded-circle me-3" width="56" height="56">
                        <span class="fw-bold fs-5"><?php echo htmlspecialchars($post['username']); ?></span>
                        <span class="ms-auto text-muted small align-self-end"><?php echo $post['created_at']; ?></span>
                    </div>
                    <div class="card-body">
                        <p class="fs-5 mb-3"><?php echo nl2br(htmlspecialchars($post['text'])); ?></p>
                        <?php if ($post['image']): ?>
                            <img src="<?php echo $post['image']; ?>" class="img-fluid rounded mb-2">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header bg-white border-0"><i class="far fa-comments me-2"></i>Comments</div>
                    <div class="card-body">
                        <?php foreach ($comments as $c): ?>
                            <div class="d-flex mb-3 align-items-start">
                                <img src="<?php echo $c['profile_pic'] ? $c['profile_pic'] : 'uploads/profile_pics/default.png'; ?>" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <span class="fw-bold"><?php echo htmlspecialchars($c['username']); ?></span>
                                    <span class="text-muted small ms-2"><?php echo $c['created_at']; ?></span>
                                    <div><?php echo nl2br(htmlspecialchars($c['comment'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <form method="post">
                            <div class="mb-3 input-group">
                                <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                <textarea name="comment" class="form-control" placeholder="Add a comment..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Comment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html> 