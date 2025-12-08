<?php
$host = "localhost";
$db   = "rejestr_obecnosci";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("❌ Błąd połączenia: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$fullName    = trim($_POST['fullName'] ?? '');
$date        = $_POST['date'] ?? '';
$exitTime    = $_POST['exitTime'] ?? '';
$entryTime   = $_POST['entryTime'] ?? '';
$destination = trim($_POST['destination'] ?? '');

if ($fullName === '' || $date === '' || $exitTime === '' || $entryTime === '' || $destination === '') {
    header("Location: index1.php?status=missing");
    exit();
}

$exitDT  = DateTime::createFromFormat("Y-m-d H:i", "$date $exitTime");
$entryDT = DateTime::createFromFormat("Y-m-d H:i", "$date $entryTime");

if (!$exitDT || !$entryDT || $entryDT <= $exitDT) {
    header("Location: index1.php?status=invalid-order");
    exit();
}


$checkUser = $conn->prepare("SELECT id FROM employees WHERE full_name = ?");
$checkUser->bind_param("s", $fullName);
$checkUser->execute();
$resultUser = $checkUser->get_result();

if ($resultUser->num_rows === 0) {
    header("Location: index1.php?status=unknown-user");
    exit();
}
$checkUser->close();

$checkDup = $conn->prepare("SELECT id FROM ewidencja WHERE imie_nazwisko = ? AND data = ? AND godzina_wyjscia = ? AND godzina_przyjscia = ?");
$checkDup->bind_param("ssss", $fullName, $date, $exitTime, $entryTime);
$checkDup->execute();
$resultDup = $checkDup->get_result();

if ($resultDup->num_rows > 0) {
    header("Location: index1.php?status=duplicate");
    exit();
}
$checkDup->close();

$stmt = $conn->prepare("INSERT INTO ewidencja (imie_nazwisko, data, godzina_wyjscia, godzina_przyjscia, cel) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    header("Location: index1.php?status=error");
    exit();
}
$stmt->bind_param("sssss", $fullName, $date, $exitTime, $entryTime, $destination);

if ($stmt->execute()) {
    header("Location: index1.php?status=success");
} else {
    header("Location: index1.php?status=error");
}

$stmt->close();
$conn->close();
exit();
?>
