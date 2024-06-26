<?php
session_start();
require '../doc/autoload.php';

// Vérifier que l'utilisateur est connecté et est un modérateur
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'moderator') {
  echo "Accès refusé. Vous devez être un modérateur pour réinitialiser les J'aime.";
  exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->mini_x->tweets;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Réinitialiser les J'aime et la liste des utilisateurs ayant liké tous les tweets
  $result = $collection->updateMany(
    [], // Filtre vide pour sélectionner tous les documents
    ['$set' => ['likes' => 0, 'liked_by' => []]]
  );
  $_SESSION['notification'] = "Tous les J'aime ont été réinitialisés.";
  header('Location: reset_likes.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Réinitialiser les J'aime</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="../styles/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include '../templates/header.php'; ?>

  <div class="container mt-4 flex-grow-1">
    <h1>Réinitialisation des J'aime</h1>

    <?php if (isset($_SESSION['notification'])) : ?>
      <div class="alert alert-success" role="alert">
        <?php echo $_SESSION['notification']; ?>
        <?php unset($_SESSION['notification']); ?>
      </div>
    <?php endif; ?>

    <form action="reset_likes.php" method="POST" class="mt-5">
      <button type="submit" class="btn btn-danger">Réinitialiser tous les J'aime</button>
    </form>
  </div>

  <?php include '../templates/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>