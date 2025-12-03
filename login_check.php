<?php
session_start();
require 'insert1.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_logged'] = true;
    header("Location: admin.php");
    exit();
}

header("Location: login.php?error=1");
exit();
?>
