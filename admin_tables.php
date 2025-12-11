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

// Połączenie z bazą
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'rejestr_obecnosci';
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Błąd połączenia: " . $conn->connect_error);
}

// Filtry
$nazwisko = $_GET['nazwisko'] ?? '';
$data_od = $_GET['data_od'] ?? '';
$data_do = $_GET['data_do'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
if (!empty($nazwisko)) $where[] = "(imie_nazwisko LIKE '%" . $conn->real_escape_string($nazwisko) . "%')";
if (!empty($data_od)) $where[] = "(data >= '" . $conn->real_escape_string($data_od) . "')";
if (!empty($data_do)) $where[] = "(data <= '" . $conn->real_escape_string($data_do) . "')";
$where_clause = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

// Pobieranie rekordów
$sql = "SELECT id, imie_nazwisko, data, godzina_wyjscia, godzina_przyjscia, cel 
        FROM ewidencja 
        $where_clause 
        ORDER BY data DESC, imie_nazwisko ASC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Liczenie wszystkich rekordów dla paginacji
$count_sql = "SELECT COUNT(*) AS total FROM ewidencja $where_clause";
$total_rows = $conn->query($count_sql)->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ewidencja wyjść służbowych</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f9; font-family:'Times New Roman', serif; }
.card { border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,0.1); padding:30px; margin:30px auto; max-width:1100px; }
h2 { font-weight:600; text-align:center; margin-bottom:30px; }
.table th, .table td { vertical-align: middle; text-align:center; }
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
        <li class="nav-item">
             <a class="nav-link active" href="admin_tables.php">Ewidencja</a>
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
        <li class="nav-item"><a class="nav-link" href="change_password.php">Zmień hasło</a></li>
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
<div class="card">
<h2>Ewidencja wyjść służbowych</h2>
<form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
        <select name="nazwisko" class="form-control">
            <option value="">-- Wybierz pracownika --</option>
            <?php
            $res_n = $conn->query("SELECT DISTINCT full_name FROM employees ORDER BY full_name ASC");
            while ($row_n = $res_n->fetch_assoc()) {
                $sel = ($nazwisko == $row_n['full_name']) ? 'selected' : '';
                echo "<option value='".htmlspecialchars($row_n['full_name'])."' $sel>".htmlspecialchars($row_n['full_name'])."</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-md-3"><input type="date" name="data_od" value="<?= htmlspecialchars($data_od) ?>" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="data_do" value="<?= htmlspecialchars($data_do) ?>" class="form-control"></div>
    <div class="col-md-2 d-grid"><button type="submit" class="btn btn-primary">Filtruj</button></div>
</form>

<?php if ($total_rows == 0): ?>
    <p class="text-center text-muted">Brak wyników pasujących do kryteriów.</p>
<?php else: ?>
<table class="table table-striped table-hover">
   <thead class="table-dark">
    <tr>
        <th>Data</th>
        <th>Imię i nazwisko</th>
        <th>Godzina wyjścia</th>
        <th>Godzina przyjścia</th>
        <th>Cel wyjścia</th>
        <th>Akcje</th>
    </tr>
</thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= date('d.m.Y', strtotime($row['data'])) ?></td>
            <td><?= htmlspecialchars($row['imie_nazwisko']) ?></td>
            <td><?= date('H:i', strtotime($row['godzina_wyjscia'])) ?></td>
            <td><?= date('H:i', strtotime($row['godzina_przyjscia'])) ?></td>
            <td><?= htmlspecialchars($row['cel']) ?></td>
            <td>
                <a href="edit_record.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edytuj</a>
                 <a href="delete_record.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                onclick="return confirm('Czy na pewno chcesz usunąć ten rekord?');">Usuń</a>
            </td>

        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
<nav aria-label="Paginacja">
    <ul class="pagination justify-content-center">
        <?php
        $query = $_GET;
        for ($i=1;$i<=$total_pages;$i++) {
            $query['page'] = $i;
            $link = '?'.http_build_query($query);
            $active = ($i == $page) ? 'active' : '';
            echo "<li class='page-item $active'><a class='page-link' href='$link'>$i</a></li>";
        }
        ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>
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
