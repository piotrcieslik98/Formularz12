<?php
// Jeśli timeout → wyczyść błędną sesję
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    session_start();
    session_unset();
    session_destroy();
}

// Start nowej sesji na stronie logowania
session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Logowanie administratora</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f4f6f9;
    font-family: 'Times New Roman', sans-serif;
}
.card {
    max-width: 400px;
    margin: 100px auto;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
</style>
</head>
<body>
<div class="card p-4">
    <h3 class="text-center mb-3">Panel administratora</h3>

    <?php if (isset($_GET['timeout']) && $_GET['timeout'] == 1): ?>
        <div class="alert alert-warning text-center">
            Zostałeś wylogowany z powodu 10 minut braku aktywności.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">Nieprawidłowy login lub hasło.</div>
    <?php endif; ?>

    <form action="login_check.php" method="POST">
        <div class="mb-3">
            <label>Login</label>
            <input type="text" class="form-control" name="username" required>
        </div>
        <div class="mb-3">
            <label>Hasło</label>
            <input type="password" class="form-control" name="password" required>
        </div>
        <button class="btn btn-primary w-100">Zaloguj</button>
    </form>
</div>
</body>
</html>
