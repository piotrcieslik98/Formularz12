<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}
require 'insert1.php';
$full_name = trim($_POST['full_name']);
$stmt = $pdo->prepare("INSERT INTO employees (full_name) VALUES (?)");
$stmt->execute([$full_name]);
header("Location: employees.php");
exit();
