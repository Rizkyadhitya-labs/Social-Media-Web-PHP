<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) && !isset($_COOKIE['rememberme'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'] ?? $_COOKIE['rememberme'];

if (!isset($_GET['id'])) {
    header('Location: feed.php');
    exit;
}
$post_id = (int)$_GET['id'];

// Ambil data post
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? AND user_id = ?');
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();
if (!$post) {
    echo 'Post tidak ditemukan atau Anda tidak berhak mengedit.';
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['text']);
    $image = $post['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            // Hapus gambar lama jika ada
            if ($image && file_exists($image)) {
                unlink($image);
            }
            $image = 'uploads/post_images/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $image);
        } else {
            $errors[] = 'Tipe gambar tidak valid.';
        }
    }
    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE posts SET text = ?, image = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$text, $image, $post_id, $user_id]);
        header('Location: feed.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 shadow">
                <h2 class="mb-4 text-center">Edit Post</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                    </div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Text</label>
                        <textarea name="text" class="form-control" required><?php echo htmlspecialchars($post['text']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label><br>
                        <?php if ($post['image']): ?>
                            <img src="<?php echo $post['image']; ?>" class="img-fluid rounded mb-2" style="max-width:150px;">
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control mt-2">
                        <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html> 