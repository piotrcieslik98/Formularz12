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
    header("Location: login.php?timeout=1");
    exit();
}


$_SESSION['last_activity'] = time();

require 'insert1.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Edytuj pracownika</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f9; font-family:'Times New Roman', serif; }
.card { border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,0.1); }
.navbar .timer { color: #ffc107; margin-left: 10px; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="admin.php">Panel administratora</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
            aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="admin.php">Lista obecności</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="employees.php">Pracownicy</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="employee_add.php">Dodaj pracownika</a>
        </li>
        <li class="nav-item">
          <span class="nav-link timer" id="session-timer"></span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Wyloguj</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <h3>Edytuj pracownika</h3>
    <form action="employee_edit_save.php" method="POST">
        <input type="hidden" name="id" value="<?= $employee['id'] ?>">

        <div class="mb-3">
            <label>Imię i nazwisko</label>
            <input type="text" name="full_name" class="form-control" required value="<?= $employee['full_name'] ?>">
        </div>

        <button class="btn btn-primary">Zapisz zmiany</button>
        <a href="employees.php" class="btn btn-secondary">Powrót</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

let remaining = <?php echo $timeout; ?>;

function updateTimer() {
    let minutes = Math.floor(remaining / 60);
    let seconds = remaining % 60;
    document.getElementById('session-timer').textContent = `Wylogowanie za: ${minutes}:${seconds < 10 ? '0'+seconds : seconds}`;
    
    if (remaining <= 0) {
        window.location.href = 'logout.php';
    } else {
        remaining--;
    }
}

setInterval(updateTimer, 1000);
updateTimer();
</script>
</body>
</html>
