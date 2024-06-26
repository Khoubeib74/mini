<?php
session_start();
require '../doc/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$messagesCollection = $client->mini_x->messages;
$notificationsCollection = $client->mini_x->notifications;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $pseudo = htmlspecialchars($_POST['pseudo']);
  $message = htmlspecialchars($_POST['message']);

  $messagesCollection->insertOne([
    'email' => $email,
    'pseudo' => $pseudo,
    'message' => $message,
    'timestamp' => new MongoDB\BSON\UTCDateTime()
  ]);

  // Ajouter une notification pour le modérateur
  $notificationsCollection->insertOne([
    'user' => 'moderator',  // Utilisateur fictif pour les notifications générales
    'message' => "Nouveau message reçu de $pseudo.",
    'timestamp' => new MongoDB\BSON\UTCDateTime()
  ]);

  $success = "Votre message a été envoyé avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Contacter l'administrateur</title>
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

  <div class="container mt-5">
    <h1 class="mb-4">Contacter l'administrateur</h1>
    <?php if (isset($success)) : ?>
      <div class="alert alert-success" role="alert">
        <?php echo $success; ?>
      </div>
    <?php endif; ?>
    <form action="../doc/contact_admin.php" method="POST">
      <div class="form-group">
        <label for="email">Votre adresse e-mail</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Votre adresse e-mail" required>
      </div>
      <div class="form-group">
        <label for="pseudo">Votre pseudo Mini X</label>
        <input type="text" class="form-control" id="pseudo" name="pseudo" placeholder="Votre pseudo Mini X" required>
      </div>
      <div class="form-group">
        <label for="message">Message</label>
        <textarea class="form-control" id="message" name="message" rows="5" placeholder="Expliquez la raison de votre demande de déblocage" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
  </div>

  <?php include '../templates/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>