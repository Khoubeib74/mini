<?php
session_start();
require '../doc/autoload.php';

if (!isset($_SESSION['username'])) {
  header('Location: ../doc/login.php');
  exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$usersCollection = $client->mini_x->users;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $currentPassword = $_POST['current_password'];
  $newPassword = $_POST['new_password'];
  $confirmPassword = $_POST['confirm_password'];

  $user = $usersCollection->findOne(['username' => $_SESSION['username']]);

  if ($user && password_verify($currentPassword, $user['password'])) {
    if ($newPassword === $confirmPassword) {
      $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
      $usersCollection->updateOne(
        ['username' => $_SESSION['username']],
        ['$set' => ['password' => $hashedNewPassword]]
      );
      $success = "Votre mot de passe a été modifié avec succès.";
    } else {
      $error = "Les nouveaux mots de passe ne correspondent pas.";
    }
  } else {
    $error = "Le mot de passe actuel est incorrect.";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Modifier le mot de passe</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="../styles/style.css">
  <style>
    html,
    body {
      height: 100%;
      margin: 0;
    }

    body {
      display: flex;
      flex-direction: column;
    }

    .container {
      flex: 1;
    }

    .footer {
      text-align: center;
      background-color: #f8f9fa;
      padding: 1em 0;
      flex-shrink: 0;
    }
  </style>
</head>

<body>
  <?php include '../templates/header.php'; ?>

  <div class="container mt-4">
    <h1 class="mb-4">Modifier le mot de passe</h1>
    <?php if (isset($success)) : ?>
      <div class="alert alert-success" role="alert">
        <?php echo $success; ?>
      </div>
    <?php elseif (isset($error)) : ?>
      <div class="alert alert-danger" role="alert">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>
    <form action="../doc/change_password.php" method="POST">
      <div class="form-group">
        <label for="current_password">Mot de passe actuel</label>
        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Mot de passe actuel" required>
      </div>
      <div class="form-group">
        <label for="new_password">Nouveau mot de passe</label>
        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Nouveau mot de passe" required>
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmer le nouveau mot de passe" required>
      </div>
      <button type="submit" class="btn btn-primary">Modifier</button>
    </form>
  </div>

  <?php include '../templates/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>