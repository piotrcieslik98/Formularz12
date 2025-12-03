<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

require 'insert1.php';

// Pobranie pracowników
$employees = $pdo->query("SELECT * FROM employees ORDER BY full_name")->fetchAll();

// Wybrany miesiąc i rok
$selectedMonth = $_GET['month'] ?? date("m");
$selectedYear = $_GET['year'] ?? date("Y");

// Obliczenie liczby dni w miesiącu
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

// Pobranie obecności w wybranym miesiącu
$startDate = "$selectedYear-$selectedMonth-01";
$endDate = "$selectedYear-$selectedMonth-$daysInMonth";

$stmt = $pdo->prepare("SELECT attendance.*, employees.full_name 
                       FROM attendance 
                       JOIN employees ON employees.id = attendance.employee_id
                       WHERE date BETWEEN ? AND ?");
$stmt->execute([$startDate, $endDate]);
$records = $stmt->fetchAll();

// Tworzymy tablicę obecności: $attendance[day][employee_id] = 'Imię Nazwisko'
$attendance = [];
foreach ($records as $rec) {
    $day = (int)date("j", strtotime($rec['date']));
    $attendance[$day][$rec['employee_id']] = $rec['full_name'];
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Podgląd wydruku - Lista obecności</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Times New Roman', serif; }
table { page-break-inside: auto; }
tr    { page-break-inside: avoid; page-break-after: auto; }
th, td { text-align: center; vertical-align: middle; min-width: 100px; }
@media print {
    .no-print { display: none; }
}
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
          <a class="nav-link" href="logout.php">Wyloguj</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-4">
    <h2 class="text-center mb-4">Podgląd wydruku - Lista obecności</h2>

    <form method="GET" class="row g-3 mb-4 no-print">
        <div class="col-md-3">
            <label>Miesiąc</label>
            <select class="form-select" name="month">
                <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?php echo $m; ?>" <?php if($selectedMonth==$m) echo 'selected'; ?>>
                        <?php echo date("F", mktime(0,0,0,$m,1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Rok</label>
            <input type="number" class="form-control" name="year" value="<?php echo $selectedYear; ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filtruj</button>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="button" class="btn btn-success w-100" onclick="window.print()">Drukuj</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Dzień</th>
                    <?php foreach ($employees as $emp): ?>
                        <th><?php echo $emp['full_name']; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for($day=1;$day<=$daysInMonth;$day++): ?>
                    <tr>
                        <td><?php echo $day; ?></td>
                        <?php foreach ($employees as $emp): ?>
                            <td>
                                <?php 
                                // Wyświetlamy nazwisko jeśli obecny
                                echo $attendance[$day][$emp['id']] ?? ''; 
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
