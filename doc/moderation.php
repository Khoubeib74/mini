<?php
session_start();
require '../doc/autoload.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'moderator') {
  header('Location: ../doc/index.php');
  exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$usersCollection = $client->mini_x->users;
$tweetsCollection = $client->mini_x->tweets;

// Récupérer les utilisateurs bloqués
$blockedUsers = $usersCollection->find(['blocked' => true]);

// Récupérer les tweets masqués
$hiddenTweets = $tweetsCollection->find(['hidden' => true]);

$blockedUserCount = $usersCollection->countDocuments(['blocked' => true]);
$hiddenTweetCount = $tweetsCollection->countDocuments(['hidden' => true]);

function displayNotification()
{
  if (isset($_SESSION['moderation_notification'])) {
    echo "<div class='alert alert-info alert-dismissible fade show' role='alert'>
            " . htmlspecialchars($_SESSION['moderation_notification'], ENT_QUOTES, 'UTF-8') . "
            <form action='../doc/clear_notification.php' method='POST' class='d-inline'>
              <input type='hidden' name='notification_type' value='moderation_notification'>
              <button type='submit' class='close' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
              </button>
            </form>
          </div>";
    unset($_SESSION['moderation_notification']);
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Modération</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="../styles/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include '../templates/header.php'; ?>

  <div class="container mt-4 flex-grow-1">
    <h1 class="mb-4">Modération</h1>
    <?php displayNotification(); ?>

    <h2>Utilisateurs Bloqués <span class="badge badge-secondary"><?php echo $blockedUserCount; ?></span></h2>
    <div class="table-responsive mb-4">
      <table class="table table-bordered">
        <thead class="thead-dark">
          <tr>
            <th scope="col">Nom d'utilisateur</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($blockedUsers as $user) : ?>
            <tr>
              <td><?php echo htmlspecialchars($user['username']); ?></td>
              <td>
                <form action="../doc/unblock_user.php" method="POST" class="d-inline unblock-form">
                  <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                  <button type="submit" class="btn btn-outline-success btn-sm">Débloquer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($blockedUserCount == 0) : ?>
            <tr>
              <td colspan="2" class="text-center">Aucun utilisateur bloqué.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <h2>Tweets Masqués <span class="badge badge-secondary"><?php echo $hiddenTweetCount; ?></span></h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="thead-dark">
          <tr>
            <th scope="col">Utilisateur</th>
            <th scope="col">Message</th>
            <th scope="col">Date</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($hiddenTweets as $tweet) : ?>
            <tr>
              <td><?php echo htmlspecialchars($tweet['user']); ?></td>
              <td><?php echo htmlspecialchars($tweet['message']); ?></td>
              <td><?php echo $tweet['timestamp']->toDateTime()->format('Y-m-d H:i:s'); ?></td>
              <td>
                <form action="../doc/unhide_tweet.php" method="POST" class="d-inline unhide-form">
                  <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
                  <button type="submit" class="btn btn-outline-success btn-sm">Démasquer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($hiddenTweetCount == 0) : ?>
            <tr>
              <td colspan="4" class="text-center">Aucun tweet masqué.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php include '../templates/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    // Fonction pour soumettre les formulaires via AJAX
    function ajaxFormSubmit(formClass) {
      $(document).on('submit', formClass, function(event) {
        event.preventDefault();
        var $form = $(this);
        $.ajax({
          type: $form.attr('method'),
          url: $form.attr('action'),
          data: $form.serialize(),
          success: function(response) {
            // Traiter la réponse ici si nécessaire
            console.log('Form submitted successfully');
            // Actualiser la section de modération si nécessaire
            location.reload();
          },
          error: function(xhr, status, error) {
            console.error('Form submission failed: ' + error);
          }
        });
      });
    }

    // Appliquer la fonction ajaxFormSubmit à tous les formulaires
    ajaxFormSubmit('.unblock-form');
    ajaxFormSubmit('.unhide-form');
  </script>
</body>

</html>