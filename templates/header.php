<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require '../doc/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");

// Compter les notifications pour l'utilisateur connecté
$notificationsCollection = $client->mini_x->notifications;
$userNotifications = 0;

if (isset($_SESSION['username'])) {
  $userNotifications = $notificationsCollection->countDocuments(['user' => $_SESSION['username']]);
}

// Variables pour les modérateurs
$blockedUserCount = 0;
$hiddenTweetCount = 0;
$moderatorMessages = 0;

if (isset($_SESSION['username']) && $_SESSION['role'] === 'moderator') {
  $usersCollection = $client->mini_x->users;
  $tweetsCollection = $client->mini_x->tweets;
  $messagesCollection = $client->mini_x->messages;

  // Compter les utilisateurs bloqués
  $blockedUserCount = $usersCollection->countDocuments(['blocked' => true]);

  // Compter les tweets masqués
  $hiddenTweetCount = $tweetsCollection->countDocuments(['hidden' => true]);

  // Compter les messages pour le modérateur
  $moderatorMessages = $messagesCollection->countDocuments();
}
?>

<header>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="../doc/index.php">Mini X</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="../doc/index.php">Accueil</a>
        </li>
        <?php if (isset($_SESSION['username'])) : ?>
          <li class="nav-item">
            <a class="nav-link" href="../doc/notifications.php">Notifications <?php echo $userNotifications > 0 ? "($userNotifications)" : ''; ?></a>
          </li>
          <?php if ($_SESSION['role'] === 'moderator') : ?>
            <li class="nav-item">
              <a class="nav-link" href="../doc/moderation.php">Modération</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../doc/messages.php">Messages <?php echo $moderatorMessages > 0 ? "($moderatorMessages)" : ''; ?></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../doc/reset_likes.php">Réinitialiser les J'aime</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../doc/aggregation.php">Données</a>
            </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="../doc/change_password.php">Modifier le mot de passe</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../doc/logout.php">Déconnexion (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
          </li>
        <?php else : ?>
          <li class="nav-item">
            <a class="nav-link" href="../doc/login.php">Connexion</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../doc/register.php">Inscription</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
</header>