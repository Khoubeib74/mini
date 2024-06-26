<?php
session_start();
require '../doc/autoload.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'moderator') {
  header('Location: ../doc/index.php');
  exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$messagesCollection = $client->mini_x->messages;

$messages = $messagesCollection->find([], ['sort' => ['timestamp' => -1]]);

function displayNotification()
{
  if (isset($_SESSION['notification'])) {
    echo "<div class='alert alert-info' role='alert'>" . htmlspecialchars($_SESSION['notification'], ENT_QUOTES, 'UTF-8') . "</div>";
    unset($_SESSION['notification']);
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Messages des utilisateurs</title>
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
      /* Vous pouvez ajuster la couleur de fond si nécessaire */
      padding: 1em 0;
      flex-shrink: 0;
    }
  </style>
</head>

<body>
  <?php include '../templates/header.php'; ?>

  <div class="container mt-4">
    <h1>Messages des utilisateurs</h1>
    <?php displayNotification(); ?>

    <div class="list-group">
      <?php foreach ($messages as $message) : ?>
        <div class="list-group-item">
          <h5 class="mb-1">Pseudo Mini X: <?php echo htmlspecialchars(html_entity_decode($message['pseudo']), ENT_QUOTES, 'UTF-8'); ?></h5>
          <p class="mb-1">Email: <?php echo htmlspecialchars(html_entity_decode($message['email']), ENT_QUOTES, 'UTF-8'); ?></p>
          <p class="mb-1">Message: <?php echo htmlspecialchars(html_entity_decode($message['message']), ENT_QUOTES, 'UTF-8'); ?></p>
          <small><?php echo $message['timestamp']->toDateTime()->format('Y-m-d H:i:s'); ?></small>
          <form action="../doc/delete_message.php" method="POST" class="mt-2">
            <input type="hidden" name="message_id" value="<?php echo $message['_id']; ?>">
            <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php include '../templates/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>