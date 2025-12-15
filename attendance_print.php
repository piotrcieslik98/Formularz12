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
$selectedMonth = $_GET['month'] ?? date("m");
$selectedYear  = $_GET['year'] ?? date("Y");
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
$startDate = "$selectedYear-$selectedMonth-01";
$endDate   = "$selectedYear-$selectedMonth-$daysInMonth";
$stmt = $pdo->prepare("
    SELECT attendance.*, employees.full_name 
    FROM attendance 
    JOIN employees ON employees.id = attendance.employee_id
    WHERE date BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$records = $stmt->fetchAll();

$attendance = [];
foreach ($records as $rec) {
    $day = (int)date("j", strtotime($rec['date']));
    $attendance[$day][$rec['employee_id']] = true;
}

$holidayStmt = $pdo->prepare("SELECT date, code FROM holidays WHERE date BETWEEN ? AND ?");
$holidayStmt->execute([$startDate, $endDate]);
$holidays = [];
foreach ($holidayStmt->fetchAll() as $h) {
    $holidays[$h['date']] = $h['code'];
}
$chunkedEmployees = array_chunk($employees, 6);
$polishMonths = [
    1 => "Styczeń", 2 => "Luty", 3 => "Marzec", 4 => "Kwiecień",
    5 => "Maj",     6 => "Czerwiec", 7 => "Lipiec", 8 => "Sierpień",
    9 => "Wrzesień",10 => "Październik",11 => "Listopad",12 => "Grudzień"
];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Lista obecności — podgląd</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Times New Roman', serif; }
th, td { text-align: center; vertical-align: middle; width: 120px; }
.weekend td { background-color: #f2f2f2 !important; }
.weekend { background-color: #f2f2f2 !important; }
@media print {
    .no-print { display:none!important; }
    .page { page-break-after: always; }
    table td, table th { padding: 9px 10px !important; font-size: 11px !important; line-height: 1 !important; }
    table { border-collapse: collapse !important; margin: 0 !important; width: 100% !important; }
    body { margin: 0; padding: 0; }
}
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
        <li class="nav-item"><a class="nav-link" href="holidays.php">Dni wolne</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_tables.php">Ewidencja</a></li>
        <li class="nav-item"><a class="nav-link" href="attendance_add.php">Dodaj obecność</a></li>
        <li class="nav-item"><a class="nav-link active" href="attendance_print.php">Podgląd wydruku</a></li>
        <li class="nav-item"><a class="nav-link" href="admin.php">Lista obecności</a></li>
        <li class="nav-item"><a class="nav-link" href="employees.php">Pracownicy</a></li>
        <li class="nav-item"><a class="nav-link" href="change_password.php">Zmień hasło</a></li>
        <li class="nav-item"><span class="nav-link timer" id="session-timer"></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Wyloguj</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container no-print mb-4">
    <form method="GET" class="row g-3">
        <div class="col-md-3">
            <label>Miesiąc</label>
            <select class="form-select" name="month">
                <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= ($selectedMonth==$m?'selected':'') ?>><?= $polishMonths[$m] ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Rok</label>
            <input type="number" class="form-control" name="year" value="<?= $selectedYear ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filtruj</button>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="button" onclick="window.print()" class="btn btn-success w-100">Drukuj</button>
        </div>
    </form>
</div>
<?php foreach ($chunkedEmployees as $pageEmployees): ?>
<div class="page px-3">
    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>Dzień</th>
            <?php foreach ($pageEmployees as $emp): ?>
                <th><?= $emp['full_name'] ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php for ($day = 1; $day <= $daysInMonth; $day++):
           $dayDate = sprintf(
                '%04d-%02d-%02d',
                $selectedYear,
                $selectedMonth,
                $day
            );
            $weekday = date("N", strtotime($dayDate));
            $isWeekend = ($weekday == 6 || $weekday == 7);
            $today   = date("Y-m-d");
            $nowTime = date("H:i");
        ?>
            <tr class="<?= $isWeekend ? 'weekend' : '' ?>">
                <td><?= $day ?></td>
                <?php foreach ($pageEmployees as $emp):
                    $id = $emp['id'];
                    $cell = "";

                    if (isset($attendance[$day][$id])) {
                        $cell = $emp['full_name']; 
                    } elseif (isset($holidays[$dayDate])) {
                        $cell = $holidays[$dayDate]; 
                    } elseif ($isWeekend) {
                        $cell = "-"; 
                    } else {
                        if ($dayDate < $today || ($dayDate == $today && $nowTime > "09:30")) {
                            $cell = "nb"; 
                        }
                    }
                ?>
                    <td><?= $cell ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>
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
