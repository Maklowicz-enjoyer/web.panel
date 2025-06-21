<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Panel C2 - Logowanie</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      background-color: #e6f2f8;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px #ccc;
      width: 300px;
    }
  </style>
</head>
<body>
  <form class="login-box" action="dashboard.php" method="post">
    <h4 class="text-center mb-4">Panel C2</h4>
    <div class="mb-3">
      <label for="email" class="form-label">Email/Login</label>
      <input type="text" class="form-control" name="email" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Hasło</label>
      <input type="password" class="form-control" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Zaloguj</button>
  </form>
</body>
</html>