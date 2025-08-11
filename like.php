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
    // Check if already liked
    $stmt = $pdo->prepare('SELECT id FROM likes WHERE post_id = ? AND user_id = ?');
    $stmt->execute([$post_id, $user_id]);
    if ($stmt->fetch()) {
        // Unlike
        $del = $pdo->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?');
        $del->execute([$post_id, $user_id]);
    } else {
        // Like
        $ins = $pdo->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)');
        $ins->execute([$post_id, $user_id]);
    }
    // Redirect back
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: feed.php');
    }
    exit;
}
header('Location: feed.php');
exit; 