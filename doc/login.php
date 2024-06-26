<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $collection = $client->mini_x->users;

  $username = $_POST['username'];
  $password = $_POST['password'];

  $user = $collection->findOne(['username' => $username]);

  // Check if the user exists, the password matches, and the user is not blocked
  if ($user && password_verify($password, $user['password'])) {
    if (isset($user['blocked']) && $user['blocked'] === true) {
      $error = "Votre compte a été bloqué. Veuillez contacter l'administrateur. <a href='../doc/contact_admin.php'>Contactez l'administrateur</a>";
    } else {
      $_SESSION['username'] = $username;
      $_SESSION['role'] = $user['role'];
      header('Location: ../doc/index.php');
      exit();
    }
  } else {
    $error = "Nom d'utilisateur ou mot de passe incorrect";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Connexion</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="../styles/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include '../templates/header.php'; ?>

  <div class="container mt-5 flex-grow-1">
    <h1 class="mb-4">Connexion</h1>
    <?php if (isset($error)) : ?>
      <div class="alert alert-danger" role="alert">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>
    <form action="../doc/login.php" method="POST">
      <div class="form-group">
        <label for="username">Nom d'utilisateur</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Nom d'utilisateur" required>
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
      </div>
      <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>
  </div>

  <?php include '../templates/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>