<?php
session_start();
require '../doc/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->mini_x->tweets;
$tweets = $collection->find(['hidden' => ['$ne' => true]], ['sort' => ['timestamp' => -1]]);

function displayNotification()
{
  if (isset($_SESSION['notification'])) {
    echo "<div class='alert alert-info alert-dismissible fade show' role='alert'>
            {$_SESSION['notification']}
            <form action='../doc/clear_notification.php' method='POST' class='d-inline'>
              <input type='hidden' name='notification_type' value='notification'>
              <button type='submit' class='close' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
              </button>
            </form>
          </div>";
    unset($_SESSION['notification']);
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Mini X</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="../styles/style.css">
  <style>
    #current-time {
      font-weight: bold;
      transition: color 0.5s ease-in-out;
    }

    .color1 {
      color: #DC143C;
      /* Crimson Red */
    }

    .color2 {
      color: #4169E1;
      /* Royal Blue */
    }

    .color3 {
      color: #50C878;
      /* Emerald Green */
    }

    .color4 {
      color: #FF8C00;
      /* Tangerine Orange */
    }

    .color5 {
      color: #800080;
      /* Deep Purple */
    }

    .tweet-btn-group .btn {
      flex: 1 1 auto;
      min-width: 100px;
    }
  </style>
</head>

<body>
  <?php include '../templates/header.php'; ?>

  <div class="d-flex flex-column min-vh-100">
    <div class="container mt-4 flex-grow-1">
      <h1 class="mb-4">Bienvenue sur Mini X</h1>
      <div id="current-time" class="mb-4"></div> <!-- Ajout de l'élément pour afficher l'heure -->
      <?php displayNotification(); ?>
      <?php if (isset($_SESSION['username'])) : ?>
        <form action="../doc/create_tweet.php" method="POST" class="mb-4">
          <div class="form-group">
            <textarea name="message" class="form-control" placeholder="Quoi de neuf ?" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Tweeter</button>
        </form>
      <?php else : ?>
        <p>Veuillez vous <a href="../doc/login.php">connecter</a> pour tweeter.</p>
      <?php endif; ?>

      <form action="../doc/search.php" method="GET" class="mb-4">
        <div class="input-group">
          <input type="text" name="query" class="form-control" placeholder="Rechercher des tweets">
          <div class="input-group-append">
            <button type="submit" class="btn btn-outline-secondary">Rechercher</button>
          </div>
        </div>
      </form>

      <?php foreach ($tweets as $tweet) : ?>
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">
              <a href="../doc/profile.php?username=<?php echo htmlspecialchars($tweet['user']); ?>">
                <?php echo htmlspecialchars($tweet['user']); ?>
              </a>
            </h5>
            <p class="card-text"><?php echo htmlspecialchars($tweet['message']); ?></p>
            <p class="card-text"><small class="text-muted"><?php echo $tweet['timestamp']->toDateTime()->format('Y-m-d H:i:s'); ?></small></p>
            <p class="card-text">J'aime: <?php echo $tweet['likes'] ?? 0; ?></p>
            <?php if (isset($_SESSION['username'])) : ?>
              <?php if (!isset($tweet['liked_by']) || !in_array($_SESSION['username'], iterator_to_array($tweet['liked_by']))) : ?>
                <form action="../doc/like_tweet.php" method="POST" class="d-inline like-form">
                  <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
                  <input type="hidden" name="action" value="like">
                  <button type="submit" class="btn btn-outline-primary btn-sm">Like</button>
                </form>
              <?php else : ?>
                <form action="../doc/like_tweet.php" method="POST" class="d-inline unlike-form">
                  <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
                  <input type="hidden" name="action" value="unlike">
                  <button type="submit" class="btn btn-outline-danger btn-sm">Don't Like</button>
                </form>
              <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($tweet['retweet']) && $tweet['retweet']) : ?>
              <p><em>Retweeté de <?php echo htmlspecialchars($tweet['original_user']); ?></em></p>
            <?php endif; ?>
            <?php if (isset($tweet['comments'])) : ?>
              <div class="comments-section mt-3">
                <h6>Commentaires</h6>
                <?php foreach ($tweet['comments'] as $comment) : ?>
                  <div class="border p-2 my-2 comment">
                    <p><strong><?php echo htmlspecialchars($comment['user']); ?></strong>: <?php echo htmlspecialchars($comment['message']); ?> <em><?php echo $comment['timestamp']->toDateTime()->format('Y-m-d H:i:s'); ?></em></p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['username'])) : ?>
              <form action="../doc/comment_tweet.php" method="POST" class="mt-2 comment-form">
                <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
                <div class="form-group">
                  <textarea name="comment" class="form-control" placeholder="Ajouter un commentaire" required></textarea>
                </div>
                <button type="submit" class="btn btn-outline-secondary btn-sm tweet-btn">Commenter</button>
              </form>
            <?php endif; ?>

            <div class="btn-group tweet-btn-group mt-2" role="group">
              <?php if (isset($_SESSION['username']) && ($_SESSION['username'] == $tweet['user'] || $_SESSION['role'] == 'moderator')) : ?>
                <form action="../doc/delete_tweet.php" method="POST" class="d-inline delete-form">
                  <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
                  <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                </form>
              <?php endif; ?>

              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'moderator' && $tweet['user'] !== $_SESSION['username']) : ?>
                <form action="../doc/hide_tweet.php" method="POST" class="d-inline hide-form">
                  <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
                  <button type="submit" class="btn btn-outline-warning btn-sm">Masquer</button>
                </form>
              <?php endif; ?>

              <?php if (isset($_SESSION['username']) && $_SESSION['username'] == $tweet['user'] && !isset($tweet['retweet'])) : ?>
                <form action="../doc/edit_tweet.php" method="POST" class="d-inline edit-form">
                  <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
                </form>
              <?php endif; ?>

              <?php if (isset($_SESSION['username']) && $_SESSION['username'] != $tweet['user']) : ?>
                <form action="../doc/retweet.php" method="POST" class="d-inline retweet-form">
                  <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
                  <button type="submit" class="btn btn-outline-secondary btn-sm">Retweeter</button>
                </form>
              <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['username']) && $_SESSION['username'] == $tweet['user'] && !isset($tweet['retweet'])) : ?>
              <form action="../doc/edit_tweet.php" method="POST" class="mt-2 edit-form">
                <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
                <div class="form-group">
                  <textarea name="new_message" class="form-control" required><?php echo htmlspecialchars($tweet['message']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-outline-secondary btn-sm tweet-btn">Modifier</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php include '../templates/footer.php'; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    // Fonction pour mettre à jour l'heure et changer la couleur
    function updateTime() {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const currentTime = `${hours}:${minutes}:${seconds}`;
      const timeElement = document.getElementById('current-time');
      timeElement.textContent = `${currentTime}`;

      // Change la couleur à chaque seconde
      const colors = ['color1', 'color2', 'color3', 'color4', 'color5'];
      const currentColor = colors[now.getSeconds() % colors.length];
      timeElement.className = currentColor;
    }

    // Mettre à jour l'heure toutes les secondes
    setInterval(updateTime, 1000);

    // Initialiser l'heure lors du chargement de la page
    updateTime();

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
            // Actualiser la section de tweets si nécessaire
            location.reload();
          },
          error: function(xhr, status, error) {
            console.error('Form submission failed: ' + error);
          }
        });
      });
    }

    // Appliquer la fonction ajaxFormSubmit à tous les formulaires
    ajaxFormSubmit('.like-form');
    ajaxFormSubmit('.unlike-form');
    ajaxFormSubmit('.hide-form');
    ajaxFormSubmit('.retweet-form');
    ajaxFormSubmit('.comment-form');
    ajaxFormSubmit('.delete-form');
    ajaxFormSubmit('.edit-form');
  </script>
</body>

</html>