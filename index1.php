<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rejestracja Obecności</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
      body {
          background-color: #f8f9fa;
          font-family: 'Times New Roman', sans-serif;
      }
      .form-container {
          background-color: white;
          border-radius: 16px;
          box-shadow: 0 8px 24px rgba(0,0,0,0.1);
          padding: 30px;
          max-width: 600px;
          margin: auto;
      }
      .form-label { font-weight: 500; }
      .btn-primary {
          background-color: #0d6efd;
          border: none;
          border-radius: 10px;
          padding: 12px;
          font-weight: 500;
          font-size: 16px;
      }
      .btn-primary:hover { background-color: #0b5ed7; }
      h2 { font-weight: 600; color: #343a40; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Rejestr</a>
  </div>
</nav>
    <div class="container mt-5">
        <div class="form-container">
      <h2 class="mb-4 text-center">Ewidencja wyjść w godzinach służbowych</h2>

      <?php if (isset($_GET['status'])): ?>
        <?php
          $status = $_GET['status'];
          $classMap = [
            'success' => 'alert-success',
            'missing' => 'alert-warning',
            'invalid-order' => 'alert-warning',
            'error' => 'alert-danger',
          ];
          $statusClass = $classMap[$status] ?? 'alert-info';
        ?>
        <div class="alert <?= $statusClass ?> mb-4">
          <?php
            switch ($status) {
              case 'success':
                echo '✅ Dane zostały zapisane.';
                break;
              case 'missing':
                echo '⚠️ Uzupełnij wszystkie pola.';
                break;
              case 'invalid-order':
                echo '⚠️ Godzina przyjścia musi być późniejsza niż godzina wyjścia.';
                break;
              case 'error':
                echo '❌ Błąd zapisu do bazy.';
                break;
              case 'unknown-user':
                echo '❌ Podane imię i nazwisko nie istnieje w tabeli użytkowników.';
                break;
              case 'duplicate':
                echo '⚠️ Taki wpis już istnieje (ta sama osoba, data i godziny).';
                break;
            }
          ?>
        </div>
      <?php endif; ?>

      <form action="insert2.php" method="POST" id="ewidencjaForm">
        <div class="mb-3">
          <label for="fullName" class="form-label">Imię i nazwisko</label>
          <input type="text" class="form-control" id="fullName" name="fullName" required />
        </div>

        <div class="mb-3">
          <label for="date" class="form-label">Data</label>
          <input type="date" class="form-control" id="date" name="date" required />
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="exitTime" class="form-label">Godzina wyjścia</label>
            <input type="time" class="form-control" id="exitTime" name="exitTime" required />
          </div>
          <div class="col-md-6 mb-3">
            <label for="entryTime" class="form-label">Godzina przyjścia</label>
            <input type="time" class="form-control" id="entryTime" name="entryTime" required />
          </div>
        </div>

        <div class="mb-3">
          <label for="destination" class="form-label">Dokąd / cel wyjścia służbowego</label>
          <input type="text" class="form-control" id="destination" name="destination" required />
        </div>

        <div class="d-flex flex-column flex-md-row gap-2 mt-3">
          <button type="submit" class="btn btn-primary w-100">Zapisz</button>
          <button type="button" class="btn btn-outline-secondary w-100"
          onclick="window.location.href='index1.php'">Wyczyść</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
