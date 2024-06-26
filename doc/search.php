<?php
session_start();
require '../doc/autoload.php';

// Connect to MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->mini_x->tweets;

// Retrieve search query
$query = $_GET['query'];

// Search tweets
$tweets = $collection->find([
  'message' => new MongoDB\BSON\Regex($query, 'i')
], [
  'sort' => ['timestamp' => -1],
]);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Recherche</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
  <?php include '../templates/header.php'; ?>

  <div class="container mt-4">
    <h1>Résultats de recherche pour "<?php echo htmlspecialchars($query); ?>"</h1>

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
          <p class="card-text">J'aime: <?php echo isset($tweet['likes']) ? $tweet['likes'] : 0; ?></p>
          <?php if (isset($_SESSION['username'])) : ?>
            <form action="../doc/like_tweet.php" method="POST" class="d-inline">
              <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
              <button type="submit" class="btn btn-outline-primary btn-sm">J'aime</button>
            </form>
          <?php endif; ?>

          <?php if (isset($tweet['comments']) && is_array($tweet['comments'])) : ?>
            <?php foreach ($tweet['comments'] as $comment) : ?>
              <div class="border p-2 my-2">
                <p><strong><?php echo htmlspecialchars($comment['user']); ?></strong>: <?php echo htmlspecialchars($comment['message']); ?> <em><?php echo $comment['timestamp']->toDateTime()->format('Y-m-d H:i:s'); ?></em></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php if (isset($_SESSION['username'])) : ?>
            <form action="../doc/comment_tweet.php" method="POST" class="mt-2">
              <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
              <div class="form-group">
                <textarea name="comment" class="form-control" placeholder="Ajouter un commentaire" required></textarea>
              </div>
              <button type="submit" class="btn btn-outline-secondary btn-sm">Commenter</button>
            </form>
          <?php endif; ?>

          <?php if (isset($_SESSION['username']) && ($_SESSION['username'] == $tweet['user'] || $_SESSION['role'] == 'moderator')) : ?>
            <form action="../doc/delete_tweet.php" method="POST" class="d-inline">
              <input type="hidden" name="tweet_id" value="<?php echo $tweet['_id']; ?>">
              <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php include '../templates/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>