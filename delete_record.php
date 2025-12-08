<?php
session_start();

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'rejestr_obecnosci';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) die("Błąd połączenia: " . $conn->connect_error);

$id = intval($_GET['id']);

$sql = "DELETE FROM ewidencja WHERE id = $id";

$conn->query($sql);

header("Location: admin_tables.php?deleted=1");
exit();
