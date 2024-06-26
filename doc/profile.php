<?php
session_start();
require '../doc/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$usersCollection = $client->mini_x->users;
$followsCollection = $client->mini_x->follows;

$username = $_GET['username'];
$user = $usersCollection->findOne(['username' => $username]);

if (!$user) {
  die("Utilisateur introuvable");
}

$isFollowing = false;
if (isset($_SESSION['username'])) {
  $isFollowing = $followsCollection->findOne(['follower' => $_SESSION['username'], 'following' => $username]);
}

$followersCount = $followsCollection->countDocuments(['following' => $username]);

function displayNotification()
{
  if (isset($_SESSION['notification'])) {
    echo "<div class='alert alert-info' role='alert'>{$_SESSION['notification']}</div>";
    unset($_SESSION['notification']);
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Profil de <?php echo htmlspecialchars($username); ?></title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="../styles/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include '../templates/header.php'; ?>

  <div class="container mt-4 flex-grow-1">
    <h1>Profil de <?php echo htmlspecialchars($username); ?></h1>
    <?php displayNotification(); ?>

    <p><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Nombre de followers :</strong> <?php echo $followersCount; ?></p>

    <?php if (isset($_SESSION['username']) && $_SESSION['username'] !== $username) : ?>
      <form action="../doc/follow_user.php" method="POST" class="mt-3">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
        <button type="submit" name="action" value="<?php echo $isFollowing ? 'unfollow' : 'follow'; ?>" class="btn btn-<?php echo $isFollowing ? 'danger' : 'primary'; ?>">
          <?php echo $isFollowing ? 'UnFollow' : 'Follow'; ?>
        </button>
      </form>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'moderator') : ?>
      <form action="../doc/block_user.php" method="POST" class="mt-3">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
        <button type="submit" name="action" value="<?php echo isset($user['blocked']) && $user['blocked'] ? 'unblock' : 'block'; ?>" class="btn btn-<?php echo isset($user['blocked']) && $user['blocked'] ? 'success' : 'danger'; ?>">
          <?php echo isset($user['blocked']) && $user['blocked'] ? 'Débloquer' : 'Bloquer'; ?>
        </button>
      </form>
    <?php endif; ?>
  </div>

  <?php include '../templates/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>