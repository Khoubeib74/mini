<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $collection = $client->mini_x->users;

  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
  $role = 'user'; // Par défaut, le rôle est utilisateur

  // Si un administrateur ou modérateur est connecté, permettre de définir le rôle
  if (isset($_SESSION['role']) && ($_SESSION['role'] === 'moderator' || $_SESSION['role'] === 'admin')) {
    $role = $_POST['role'];
  }

  $collection->insertOne([
    'username' => $username,
    'password' => $password,
    'role' => $role
  ]);

  $_SESSION['notification'] = "Inscription réussie! Veuillez vous connecter.";
  header('Location: ../doc/login.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Inscription</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="../styles/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include '../templates/header.php'; ?>

  <div class="container mt-5 flex-grow-1">
    <h1 class="mb-4">Inscription</h1>
    <form action="../doc/register.php" method="POST">
      <div class="form-group">
        <label for="username">Nom d'utilisateur</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Nom d'utilisateur" required>
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
      </div>
      <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'moderator' || $_SESSION['role'] === 'admin')) : ?>
        <div class="form-group">
          <label for="role">Rôle</label>
          <select class="form-control" id="role" name="role" required>
            <option value="user">Utilisateur</option>
            <option value="moderator">Modérateur</option>
          </select>
        </div>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary">S'inscrire</button>
    </form>
  </div>

  <?php include '../templates/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>