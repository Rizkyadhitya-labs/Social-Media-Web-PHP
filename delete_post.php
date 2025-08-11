<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) && !isset($_COOKIE['rememberme'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'] ?? $_COOKIE['rememberme'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    // Pastikan user adalah pemilik post
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? AND user_id = ?');
    $stmt->execute([$post_id, $user_id]);
    $post = $stmt->fetch();
    if ($post) {
        // Hapus gambar jika ada
        if ($post['image'] && file_exists($post['image'])) {
            unlink($post['image']);
        }
        // Hapus post (otomatis hapus likes & comments via ON DELETE CASCADE)
        $del = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $del->execute([$post_id]);
    }
}
header('Location: feed.php');
exit; 