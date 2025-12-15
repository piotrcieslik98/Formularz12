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

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? "";
    $date = $_POST['date'] ?? "";
    $description = $_POST['description'] ?? "";
    $code = $_POST['code'] ?? "";

    if ($date && $description && $code) {
        if ($id) {
            
            $stmt = $pdo->prepare("UPDATE holidays SET date=?, description=?, code=? WHERE id=?");
            $stmt->execute([$date, $description, $code, $id]);
            $message = "<div class='alert alert-success'>Dzień wolny został zaktualizowany.</div>";
        } else {
            
            $stmt = $pdo->prepare("INSERT INTO holidays (date, description, code) VALUES (?, ?, ?)");
            $stmt->execute([$date, $description, $code]);
            $message = "<div class='alert alert-success'>Dzień wolny został dodany.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Wszystkie pola są wymagane.</div>";
    }
}

if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM holidays WHERE id=?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: holidays.php");
    exit();
}

$holidays = $pdo->query("SELECT * FROM holidays ORDER BY date DESC")->fetchAll();
$editHoliday = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM holidays WHERE id=?");
    $stmt->execute([$_GET['edit_id']]);
    $editHoliday = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Dni wolne / Święta</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
body { font-family:'Times New Roman', serif; background: #f4f6f9; }
.card { border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,0.1); margin-bottom: 20px; }
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
        <li class="nav-item"><a class="nav-link active" href="holidays.php">Dni wolne</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_tables.php">Ewidencja</a></li>
        <li class="nav-item"><a class="nav-link" href="attendance_add.php">Dodaj obecność</a></li>
        <li class="nav-item"><a class="nav-link" href="attendance_print.php">Podgląd wydruku</a></li>
        <li class="nav-item"><a class="nav-link" href="admin.php">Lista obecności</a></li>
        <li class="nav-item"><a class="nav-link" href="employees.php">Pracownicy</a></li>
        <li class="nav-item"><a class="nav-link" href="change_password.php">Zmień hasło</a></li>
        <li class="nav-item"><span class="nav-link timer" id="session-timer"></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Wyloguj</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">

    <?= $message ?>
    <div class="card p-4">
        <h3 class="mb-3"><?= $editHoliday ? "Edytuj dzień wolny" : "Dodaj dzień wolny" ?></h3>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editHoliday['id'] ?? "" ?>">
            <div class="mb-3">
                <label>Data</label>
                <input type="date" name="date" class="form-control" value="<?= $editHoliday['date'] ?? "" ?>" required>
            </div>
            <div class="mb-3">
                <label>Nazwa / opis</label>
                <input type="text" name="description" class="form-control" value="<?= $editHoliday['description'] ?? "" ?>" required>
            </div>
            <div class="mb-3">
                <label>Symbol na liście obecności</label>
                <input type="text" name="code" class="form-control" value="<?= $editHoliday['code'] ?? "" ?>" required>
            </div>
            <button class="btn btn-primary w-100"><?= $editHoliday ? "Zaktualizuj" : "Dodaj" ?></button>
            <?php if($editHoliday): ?>
                <a href="holidays.php" class="btn btn-primary w-100 mt-2">Anuluj</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card p-4">
        <h3 class="mb-3">Lista dni wolnych</h3>
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Data</th>
                    <th>Nazwa / opis</th>
                    <th>Symbol</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($holidays)): ?>
                    <tr><td colspan="5" class="text-center">Brak dni wolnych</td></tr>
                <?php else: ?>
                    <?php foreach($holidays as $i => $h): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= $h['date'] ?></td>
                            <td><?= htmlspecialchars($h['description']) ?></td>
                            <td><?= htmlspecialchars($h['code']) ?></td>
                            <td>
                                <a href="?edit_id=<?= $h['id'] ?>" class="btn btn-warning btn-sm">Edytuj</a>
                                <a href="?delete_id=<?= $h['id'] ?>" 
                                   onclick="return confirm('Czy na pewno chcesz usunąć ten wpis?');" 
                                   class="btn btn-danger btn-sm">Usuń</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
