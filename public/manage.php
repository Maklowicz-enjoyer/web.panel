<?php
$computer = $_GET['computer'] ?? 'Nieznany';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Panel C2 - <?= htmlspecialchars($computer) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .chat-box {
      height: 400px;
      overflow-y: scroll;
      background: #f1f1f1;
      padding: 10px;
      border-radius: 8px;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
      <a href="dashboard.php" class="btn btn-secondary">⬅ Wróć</a>
      <a href="logout.php" class="btn btn-danger">Wyloguj</a>
    </div>

    <h4>Komputer: <?= htmlspecialchars($computer) ?></h4>

    <div class="chat-box mt-3 mb-3">
      <div><strong>Windows:</strong> Gotowy do przyjęcia polecenia...</div>
    </div>

    <form method="post">
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Wpisz komendę..." name="command">
        <button class="btn btn-primary" type="submit">Wyślij</button>
      </div>
    </form>
  </div>
</body>
</html>