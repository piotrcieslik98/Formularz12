<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

require 'insert1.php';

$employees = $pdo->query("SELECT * FROM employees ORDER BY full_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Pracownicy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f9; font-family:'Times New Roman', serif; }
.card { border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,0.1); }
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
          <a class="nav-link" href="logout.php">Wyloguj</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between mb-3">
        <h3>Lista pracowników</h3>
        <a href="employee_add.php" class="btn btn-primary">+ Dodaj pracownika</a>
    </div>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Imię i nazwisko</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $i => $row): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= $row['full_name'] ?></td>
                    <td>
                        <a href="employee_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edytuj</a>
                        <a href="employee_delete.php?id=<?= $row['id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Na pewno usunąć?');">Usuń</a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
