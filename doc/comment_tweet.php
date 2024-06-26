<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username'])) {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $tweetsCollection = $client->mini_x->tweets;
  $notificationsCollection = $client->mini_x->notifications;

  $tweetId = new MongoDB\BSON\ObjectId($_POST['tweet_id']);
  $user = $_SESSION['username'];
  $comment = $_POST['comment'];

  $result = $tweetsCollection->updateOne(
    ['_id' => $tweetId],
    ['$push' => ['comments' => [
      'user' => $user,
      'message' => $comment,
      'timestamp' => new MongoDB\BSON\UTCDateTime()
    ]]]
  );

  if ($result->getModifiedCount() === 0) {
    $_SESSION['notification'] = "Erreur lors de l'ajout du commentaire.";
    header('Location: ../doc/index.php');
    exit();
  }

  $tweet = $tweetsCollection->findOne(['_id' => $tweetId]);

  if ($tweet && $tweet['user'] != $user) {
    $notificationsCollection->insertOne([
      'user' => $tweet['user'],
      'message' => "$user a commenté votre tweet.",
      'timestamp' => new MongoDB\BSON\UTCDateTime()
    ]);
  }

  $_SESSION['notification'] = "Commentaire ajouté avec succès!";
  header('Location: ../doc/index.php');
  exit();
} else {
  $_SESSION['notification'] = "Une erreur s'est produite.";
  header('Location: ../doc/index.php');
  exit();
}
