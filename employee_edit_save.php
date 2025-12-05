<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}
require 'insert1.php';
$id = $_POST['id'];
$full_name = trim($_POST['full_name']);
$stmt = $pdo->prepare("UPDATE employees SET full_name = ? WHERE id = ?");
$stmt->execute([$full_name, $id]);
header("Location: employees.php");
exit();
