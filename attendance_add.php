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

$employees = $pdo->query("SELECT * FROM employees ORDER BY full_name")->fetchAll();

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'] ?? '';
    $date = $_POST['date'] ?? '';
    if ($employee_id && $date) {
        $formattedDate = date('d-m-Y', strtotime($date));
        $dayOfWeek = date('N', strtotime($date)); 
        if ($dayOfWeek >= 6) {
            $message = "<div class='alert alert-danger'>
                ❌ Nie można dodać obecności.<br>
                Data <b>$formattedDate</b> przypada w weekend.
            </div>";
        } else {
            $h = $pdo->prepare("SELECT * FROM holidays WHERE date = ?");
            $h->execute([$date]);
            $holiday = $h->fetch();
            if ($holiday) {
                $message = "<div class='alert alert-warning'>
                    ❌ Nie można dodać obecności.<br>
                    Dzień <b>$formattedDate</b> jest dniem wolnym:
                    <b>{$holiday['code']}</b> — {$holiday['description']}
                </div>";
            } else {
                $stmt = $pdo->prepare(
                    "SELECT id FROM attendance WHERE employee_id = ? AND date = ?"
                );
                $stmt->execute([$employee_id, $date]);
                if ($stmt->fetch()) {
                    $message = "<div class='alert alert-warning'>
                        ⚠️ Obecność dla dnia <b>$formattedDate</b> już istnieje.
                    </div>";
                } else {
                    $stmt = $pdo->prepare(
                        "INSERT INTO attendance (employee_id, date) VALUES (?, ?)"
                    );
                    $stmt->execute([$employee_id, $date]);
                    $message = "<div class='alert alert-success'>
                        ✅ Obecność została zapisana.
                    </div>";
                }
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>
            Wybierz pracownika i datę.
        </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dodaj obecność</title>
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
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarMenu">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="holidays.php">Dni wolne</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_tables.php">Ewidencja</a></li>
            <li class="nav-item"><a class="nav-link active" href="attendance_add.php">Dodaj obecność</a></li>
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
<h3 class="mb-4">Dodaj obecność</h3>
<?= $message ?>
<form method="POST" class="card p-4">
    <div class="mb-3">
        <label class="form-label">Pracownik</label>
        <select name="employee_id" class="form-select" required>
            <option value="">— wybierz —</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?= $emp['id'] ?>"><?= $emp['full_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Data obecności</label>
        <input type="date" name="date" class="form-control" required>
    </div>
    <button class="btn btn-primary w-100">Zapisz obecność</button>
</form>
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
