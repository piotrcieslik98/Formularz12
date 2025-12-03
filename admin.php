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

$selectedDate = $_GET['date'] ?? date("Y-m-d");
$selectedEmployee = $_GET['employee'] ?? "";

$query = "SELECT attendance.*, employees.full_name 
          FROM attendance 
          JOIN employees ON employees.id = attendance.employee_id
          WHERE date = ?";

$params = [$selectedDate];
if (!empty($selectedEmployee)) {
    $query .= " AND employee_id = ?";
    $params[] = $selectedEmployee;
}
$query .= " ORDER BY time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Panel administratora</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
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

<div class="container">
    <div class="card p-4">
        <h2 class="mb-4 text-center">Lista obecności</h2>
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Data</label>
                <input type="date" class="form-control" name="date" value="<?php echo $selectedDate; ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Pracownik</label>
                <select class="form-select" name="employee">
                    <option value="">Wszyscy</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo $emp['id']; ?>" 
                            <?php if ($selectedEmployee == $emp['id']) echo 'selected'; ?>>
                            <?php echo $emp['full_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filtruj</button>
            </div>
        </form>

        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Imię i nazwisko</th>
                    <th>Data</th>
                    <th>Godzina</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="4" class="text-center">Brak wpisów</td></tr>
                <?php else: ?>
                    <?php foreach ($records as $i => $row): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['time']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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
