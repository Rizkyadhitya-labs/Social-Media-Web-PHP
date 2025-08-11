<?php
session_start();
$_SESSION = [];
session_destroy();
setcookie('rememberme', '', time() - 3600, "/");
header('Location: login.php');
exit; 