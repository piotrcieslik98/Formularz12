<?php
require 'insert1.php'; // połączenie do bazy

// Pobranie imienia i nazwiska
$fullName = trim($_POST['fullName'] ?? '');

if (empty($fullName)) {
    header("Location: index.php?status=missing-name");
    exit();
}

date_default_timezone_set('Europe/Warsaw');

// Sprawdzenie czy dziś jest weekend
$dayOfWeek = date('N'); // 1–5 = pon-pt
if ($dayOfWeek >= 6) {
    header("Location: index.php?status=weekend-block");
    exit();
}

// Sprawdzenie godziny
$currentTime = date('H:i');
$start = "07:30";
$end = "09:30";

if ($currentTime < $start || $currentTime > $end) {
    header("Location: index.php?status=time-block");
    exit();
}

// Sprawdzenie czy pracownik istnieje
$checkUser = $pdo->prepare("SELECT id FROM employees WHERE full_name = ?");
$checkUser->execute([$fullName]);
$user = $checkUser->fetch();

if (!$user) {
    header("Location: index.php?status=unknown-user");
    exit();
}

$employeeId = $user['id'];
$today = date("Y-m-d");

// Sprawdzenie czy obecność już była zapisana dzisiaj
$checkPresence = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
$checkPresence->execute([$employeeId, $today]);
$exists = $checkPresence->fetch();

if ($exists) {
    header("Location: index.php?status=duplicate-entry");
    exit();
}

// Zapis nowej obecności
$insert = $pdo->prepare("INSERT INTO attendance (employee_id, date, time) VALUES (?, ?, ?)");
$success = $insert->execute([$employeeId, $today, date("H:i:s")]);

if ($success) {
    header("Location: index.php?status=success");
} else {
    header("Location: index.php?status=insert-error");
}
exit();
?>
