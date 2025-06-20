<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Panel C2 - Komputery</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
      <a href="index.php" class="btn btn-secondary">⬅ Wróć</a>
      <a href="logout.php" class="btn btn-danger">Wyloguj</a>
    </div>

    <h3>Wybierz komputer:</h3>
    <div class="list-group mt-4">
      <a href="manage.php?computer=PC01" class="list-group-item list-group-item-action">PC01 - Jan Kowalski</a>
      <a href="manage.php?computer=PC02" class="list-group-item list-group-item-action">PC02 - Anna Nowak</a>
      <a href="manage.php?computer=PC03" class="list-group-item list-group-item-action">PC03 - Serwer testowy</a>
    </div>
  </div>
</body>
</html>