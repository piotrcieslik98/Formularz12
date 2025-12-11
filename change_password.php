<?php
session_start();
require 'insert1.php';

$timeout = 600;

// Sprawdzenie czy admin jest zalogowany
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

// Timeout sesji
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

// Pobranie danych zalogowanego admina
$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header("Location: login.php");
    exit();
}

$message = "";

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($oldPassword && $newPassword && $confirmPassword) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();

        if (!$admin) {
            $message = "<div class='alert alert-danger'>Nie znaleziono użytkownika.</div>";
        } elseif (!password_verify($oldPassword, $admin['password'])) {
            $message = "<div class='alert alert-danger'>Nieprawidłowe stare hasło.</div>";
        } elseif ($newPassword !== $confirmPassword) {
            $message = "<div class='alert alert-danger'>Hasła nie są takie same.</div>";
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $update->execute([$hash, $admin_id]);
            $message = "<div class='alert alert-success'>Hasło zostało zmienione.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Wypełnij wszystkie pola.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Zmiana hasła — Panel administratora</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Times New Roman', serif; background:#f4f6f9; }
.card { border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,0.1); }
.navbar .timer { color: #ffc107; margin-left: 10px; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="admin.php">Panel administratora</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="holidays.php">Dni wolne</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_tables.php">Ewidencja</a></li>
        <li class="nav-item"><a class="nav-link" href="attendance_add.php">Dodaj obecność</a></li>
        <li class="nav-item"><a class="nav-link active" href="attendance_print.php">Podgląd wydruku</a></li>
        <li class="nav-item"><a class="nav-link" href="admin.php">Lista obecności</a></li>
        <li class="nav-item"><a class="nav-link" href="employees.php">Pracownicy</a></li>
        <li class="nav-item"><a class="nav-link active" href="change_password.php">Zmień hasło</a></li>
        <li class="nav-item"><span class="nav-link timer" id="session-timer"></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Wyloguj</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <div class="card p-4 mx-auto" style="max-width: 500px;">
        <h2 class="mb-4 text-center">Zmiana hasła</h2>
        <?= $message ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Stare hasło</label>
                <input type="password" name="old_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nowe hasło</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Powtórz nowe hasło</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button class="btn btn-success w-100">Zmień hasło</button>
        </form>
    </div>
</div>

<script>
let logoutTime = <?= time() + $timeout ?> * 1000;

function updateTimer() {
    let now = new Date().getTime();
    let remainingMs = logoutTime - now;

    if (remainingMs <= 0) {
        window.location.href = 'login.php?timeout=1';
    } else {
        let min = Math.floor(remainingMs / 60000);
        let sec = Math.floor((remainingMs % 60000) / 1000);
        document.getElementById('session-timer').textContent =
            `Wylogowanie za: ${min}:${sec < 10 ? '0'+sec : sec}`;
    }
}

setInterval(updateTimer, 1000);
updateTimer();
</script>

</body>
</html>
