<?php
session_start();
$timeout = 600;
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['last_activity'] = time();
require 'insert1.php';
$employees = $pdo->query("SELECT * FROM employees ORDER BY full_name")->fetchAll();
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $date = $_POST['date'];
    if (!empty($employee_id) && !empty($date)) {
        $stmt = $pdo->prepare("SELECT id FROM attendance WHERE employee_id=? AND date=?");
        $stmt->execute([$employee_id, $date]);
        $exists = $stmt->fetch();

        if ($exists) {
            $message = "<div class='alert alert-warning'>Obecność dla tego dnia już istnieje.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date) VALUES (?, ?)");
            $stmt->execute([$employee_id, $date]);
            $message = "<div class='alert alert-success'>Obecność została zapisana.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Wybierz pracownika i datę.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Dodaj obecność</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Times New Roman', serif; }
.navbar .timer { color: #ffc107; margin-left: 10px; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="admin.php">Panel administratora</a>
    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
             <a class="nav-link" href="admin_tables.php">Ewidencja</a>
        </li>
        <li class="nav-item">
             <a class="nav-link" href="attendance_add.php">Dodaj obecność</a>
        </li>
        <li class="nav-item">
             <a class="nav-link" href="attendance_print.php">Podgląd wydruku</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin.php">Lista obecności</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="employees.php">Pracownicy</a>
        </li>
        <li class="nav-item">
          <span class="nav-link timer" id="session-timer"></span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Wyloguj</a>
        </li>
    </div>
  </div>
</nav>
<div class="container mt-4">
    <h3 class="mb-4">Dodaj obecność</h3>
    <?= $message ?>
    <form method="POST" class="card p-4">
        <div class="mb-3">
            <label class="form-label">Pracownik:</label>
            <select name="employee_id" class="form-select" required>
                <option value="">-- wybierz pracownika --</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= $emp['full_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Data obecności:</label>
            <input type="date" name="date" class="form-control" required>
        </div>
        <button class="btn btn-success w-100">Zapisz obecność</button>
    </form>
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
