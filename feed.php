<?php
session_start();
require_once 'config/db.php';

// Check login
if (!isset($_SESSION['user_id']) && !isset($_COOKIE['rememberme'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'] ?? $_COOKIE['rememberme'];

// Fetch user info for navbar avatar
$stmt_user = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt_user->execute([$user_id]);
$me = $stmt_user->fetch();

// Fetch posts
$stmt = $pdo->query('SELECT posts.*, users.username, users.profile_pic FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC');
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="feed.php">MavenHart</a>
        <form class="d-flex mx-auto" action="search.php" method="get" style="max-width:400px;width:100%;">
            <input class="form-control me-2" type="search" name="q" placeholder="Search users or posts" aria-label="Search">
            <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
        </form>
        <button id="toggle-dark" class="btn btn-outline-light me-2" title="Toggle dark mode" style="border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
            <i id="dark-icon" class="fas fa-moon"></i>
        </button>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo $me['profile_pic'] ? $me['profile_pic'] : 'uploads/profile_pics/default.png'; ?>" class="avatar me-2">
                <span class="d-none d-md-inline text-white fw-bold"><?php echo htmlspecialchars($me['username']); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="profile.php?id=<?php echo $user_id; ?>"><i class="fas fa-user"></i> Profile</a></li>
                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <a href="post.php" class="btn create-post-btn w-100 mb-4"><i class="fas fa-plus-circle me-2"></i>Create New Post</a>
            <?php foreach ($posts as $post):
                // Get like count
                $like_stmt = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ?');
                $like_stmt->execute([$post['id']]);
                $like_count = $like_stmt->fetchColumn();
                // Get comment count
                $comment_stmt = $pdo->prepare('SELECT COUNT(*) FROM comments WHERE post_id = ?');
                $comment_stmt->execute([$post['id']]);
                $comment_count = $comment_stmt->fetchColumn();
                // Check if user liked
                $user_like_stmt = $pdo->prepare('SELECT id FROM likes WHERE post_id = ? AND user_id = ?');
                $user_like_stmt->execute([$post['id'], $user_id]);
                $liked = $user_like_stmt->fetch();
            ?>
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center bg-white border-0">
                        <img src="<?php echo $post['profile_pic'] ? $post['profile_pic'] : 'uploads/profile_pics/default.png'; ?>" class="rounded-circle me-3" width="56" height="56">
                        <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="text-decoration-none fw-bold fs-5">
                            <?php echo htmlspecialchars($post['username']); ?>
                        </a>
                        <span class="ms-auto text-muted small align-self-end"><?php echo $post['created_at']; ?></span>
                        <?php if ($post['user_id'] == $user_id): ?>
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning ms-2">Edit</a>
                            <form method="post" action="delete_post.php" class="ms-2 d-inline">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus postingan ini?')">Hapus</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <p class="mb-3 fs-5"><?php echo nl2br(htmlspecialchars($post['text'])); ?></p>
                        <?php if ($post['image']): ?>
                            <img src="<?php echo $post['image']; ?>" class="img-fluid rounded mb-2">
                        <?php endif; ?>
                        <div class="d-flex align-items-center mt-2">
                            <form action="like.php" method="post" class="d-inline me-2">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fa<?php echo $liked ? 's' : 'r'; ?> fa-heart like-icon"></i> <?php echo $liked ? 'Unlike' : 'Like'; ?> (<?php echo $like_count; ?>)
                                </button>
                            </form>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="far fa-comment comment-icon"></i> Comment (<?php echo $comment_count; ?>)
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Dark mode toggle
const btn = document.getElementById('toggle-dark');
const icon = document.getElementById('dark-icon');
function setDarkMode(on) {
    if(on) {
        document.body.classList.add('dark-mode');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    } else {
        document.body.classList.remove('dark-mode');
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
    }
}
// Load preference
const darkPref = localStorage.getItem('darkMode') === 'true';
setDarkMode(darkPref);
btn.onclick = function(e) {
    e.preventDefault();
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', isDark);
    setDarkMode(isDark);
};
</script>
</body>
</html> 