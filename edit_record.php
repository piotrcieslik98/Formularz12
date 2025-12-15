<?php
session_start();
$timeout = 600;

// Sprawdzenie logowania
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

// Timeout sesji
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['last_activity'] = time();

// Połączenie z bazą
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'rejestr_obecnosci';
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Błąd połączenia: " . $conn->connect_error);

$id = intval($_GET['id']);

// Pobierz rekord
$sql = "SELECT * FROM ewidencja WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows != 1) {
    die("Nie znaleziono rekordu.");
}
$record = $result->fetch_assoc();

$msg = "";

// Zapis edycji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $conn->real_escape_string($_POST['data']);
    $imie = $conn->real_escape_string($_POST['imie_nazwisko']);
    $wyjscie = $conn->real_escape_string($_POST['godzina_wyjscia']);
    $przyjscie = $conn->real_escape_string($_POST['godzina_przyjscia']);
    $cel = $conn->real_escape_string($_POST['cel']);

    $update_sql = "
        UPDATE ewidencja 
        SET data='$data', imie_nazwisko='$imie', godzina_wyjscia='$wyjscie',
            godzina_przyjscia='$przyjscie', cel='$cel'
        WHERE id=$id
    ";

    if ($conn->query($update_sql)) {
        $msg = "Dane zostały zapisane.";
        // Odśwież dane po zapisie
        $record = $conn->query($sql)->fetch_assoc();
    } else {
        $msg = "Błąd podczas zapisywania.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Edytuj rekord</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f9; font-family:'Times New Roman', serif; }
.card { border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,0.1); padding:30px; margin:30px auto; max-width:600px; }
h2 { font-weight:600; text-align:center; margin-bottom:30px; }
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

<div class="card">
<h2>Edycja rekordu</h2>

<?php if ($msg): ?>
    <div class="alert alert-info text-center"><?= $msg ?></div>
<?php endif; ?>

<form method="post">

    <label>Data</label>
    <input type="date" name="data" class="form-control mb-3" value="<?= $record['data'] ?>">

    <label>Imię i nazwisko</label>
    <select name="imie_nazwisko" class="form-control mb-3">
        <?php
        $emp = $conn->query("SELECT full_name FROM employees ORDER BY full_name ASC");
        while ($e = $emp->fetch_assoc()):
            $sel = ($record['imie_nazwisko'] == $e['full_name']) ? 'selected' : '';
        ?>
            <option value="<?= $e['full_name'] ?>" <?= $sel ?>><?= $e['full_name'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Godzina wyjścia</label>
    <input type="time" name="godzina_wyjscia" class="form-control mb-3" value="<?= $record['godzina_wyjscia'] ?>">

    <label>Godzina przyjścia</label>
    <input type="time" name="godzina_przyjscia" class="form-control mb-3" value="<?= $record['godzina_przyjscia'] ?>">

    <label>Cel</label>
    <input type="text" name="cel" class="form-control mb-4" value="<?= $record['cel'] ?>">

    <button class="btn btn-primary w-100">Zapisz</button>
    <a href="admin_tables.php" class="btn btn-secondary w-100 mt-2">Powrót</a>
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
