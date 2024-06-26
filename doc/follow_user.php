follow_user.php:

<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username'])) {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $followsCollection = $client->mini_x->follows;
  $notificationsCollection = $client->mini_x->notifications;

  $follower = $_SESSION['username'];
  $following = $_POST['username'];
  $action = $_POST['action'];

  if ($follower === $following) {
    $_SESSION['notification'] = "Vous ne pouvez pas vous suivre vous-même.";
    header('Location: ../doc/profile.php?username=' . $following);
    exit();
  }

  if ($action == 'follow') {
    $existingFollow = $followsCollection->findOne([
      'follower' => $follower,
      'following' => $following
    ]);

    if (!$existingFollow) {
      $followsCollection->insertOne([
        'follower' => $follower,
        'following' => $following
      ]);
      $_SESSION['notification'] = "Vous suivez maintenant $following.";

      // Ajouter une notification pour l'utilisateur suivi
      $notificationsCollection->insertOne([
        'user' => $following,
        'message' => "$follower a commencé à vous suivre.",
        'timestamp' => new MongoDB\BSON\UTCDateTime()
      ]);
    } else {
      $_SESSION['notification'] = "Vous suivez déjà $following.";
    }
  } elseif ($action == 'unfollow') {
    $followsCollection->deleteOne([
      'follower' => $follower,
      'following' => $following
    ]);
    $_SESSION['notification'] = "Vous avez arrêté de suivre $following.";
  }

  header('Location: ../doc/profile.php?username=' . $following);
  exit();
} else {
  $_SESSION['notification'] = "Action non autorisée.";
  header('Location: ../doc/index.php');
  exit();
}
?>