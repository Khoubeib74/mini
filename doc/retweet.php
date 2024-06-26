<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username'])) {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $tweetsCollection = $client->mini_x->tweets;
  $notificationsCollection = $client->mini_x->notifications;

  $tweetId = new MongoDB\BSON\ObjectId($_POST['tweet_id']);
  $currentUser = $_SESSION['username'];

  // Vérifier si l'utilisateur a déjà retweeté ce tweet
  $existingRetweet = $tweetsCollection->findOne([
    'user' => $currentUser,
    'retweet' => true,
    'original_tweet_id' => $tweetId
  ]);

  if ($existingRetweet) {
    $_SESSION['notification'] = "Vous avez déjà retweeté ce tweet.";
    header('Location: ../doc/index.php');
    exit();
  }

  $tweet = $tweetsCollection->findOne(['_id' => $tweetId]);

  if ($tweet && $tweet['user'] != $currentUser) {
    $tweetsCollection->insertOne([
      'user' => $currentUser,
      'message' => $tweet['message'],
      'original_user' => $tweet['user'],
      'original_tweet_id' => $tweetId,
      'timestamp' => new MongoDB\BSON\UTCDateTime(),
      'retweet' => true,
      'liked_by' => [] // Ajouter un champ liked_by vide pour chaque retweet
    ]);

    $notificationsCollection->insertOne([
      'user' => $tweet['user'],
      'message' => "$currentUser a retweeté votre tweet.",
      'timestamp' => new MongoDB\BSON\UTCDateTime()
    ]);

    $_SESSION['notification'] = "Tweet retweeté avec succès!";
  } else {
    $_SESSION['notification'] = "Vous ne pouvez pas retweeter votre propre tweet.";
  }

  header('Location: ../doc/index.php');
  exit();
}
