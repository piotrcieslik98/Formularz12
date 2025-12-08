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
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item my-1 my-lg-0 ms-lg-2">
          <a href="index1.php" class="btn btn-outline-primary w-100 w-lg-auto">Wyjścia służbowe w godzinach pracy</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-5">
    <div class="form-container">
        <h2 class="mb-4 text-center">Rejestracja obecności</h2>
        <?php if (isset($_GET['status'])): ?>
            <?php
                $status = $_GET['status'];
                $classMap = [
                    'success' => 'alert-success',
                    'missing-name' => 'alert-warning',
                    'unknown-user' => 'alert-danger',
                    'time-block' => 'alert-warning',
                    'weekend-block' => 'alert-warning',
                    'duplicate-entry' => 'alert-info',
                    'insert-error' => 'alert-danger',
                ];
                $statusClass = $classMap[$status] ?? 'alert-warning';
            ?>
            <div class="alert <?php echo $statusClass; ?> mb-4">
                <?php
                    switch ($status) {
                        case 'success':
                            echo '✅ Obecność została zapisana.';
                            break;
                        case 'missing-name':
                            echo '⚠️ Wprowadź imię i nazwisko.';
                            break;
                        case 'unknown-user':
                            echo '❌ Podane imię i nazwisko nie znajduje się w bazie.';
                            break;
                        case 'time-block':
                            echo '⏳ Rejestracja możliwa tylko między <strong>07:30 a 09:30</strong>.';
                            break;
                        case 'weekend-block':
                            echo '📅 W weekendy rejestracja obecności jest zablokowana.';
                            break;
                        case 'duplicate-entry':
                            echo 'ℹ️ Obecność dla tej osoby została już dziś zarejestrowana.';
                            break;
                        case 'insert-error':
                            echo '❌ Błąd zapisu do bazy.';
                            break;
                        default:
                            echo '⚠️ Nieznany błąd.';
                            break;
                    }
                ?>
            </div>
        <?php endif; ?>
        <form action="insert.php" method="POST">
            <div class="mb-3">
                <label for="fullName" class="form-label">Imię i nazwisko</label>
                <input type="text" class="form-control" id="fullName" name="fullName" required />
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3">
                Zarejestruj obecność
            </button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
