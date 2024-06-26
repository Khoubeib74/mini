<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username'])) {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $tweetsCollection = $client->mini_x->tweets;
  $notificationsCollection = $client->mini_x->notifications;

  $tweetId = new MongoDB\BSON\ObjectId($_POST['tweet_id']);
  $tweet = $tweetsCollection->findOne(['_id' => $tweetId]);

  if (canDeleteTweet($tweet, $_SESSION)) {
    deleteTweet($client, $tweetId);
    $_SESSION['notification'] = "Tweet et ses associations supprimés avec succès!";
  } else {
    $_SESSION['notification'] = "Vous ne pouvez pas supprimer ce tweet.";
  }

  header('Location: ../doc/index.php');
  exit();
}

function canDeleteTweet($tweet, $session)
{
  return $tweet && ($tweet['user'] == $session['username'] || $session['role'] == 'moderator');
}

function deleteTweet($client, $tweetId)
{
  $tweetsCollection = $client->mini_x->tweets;
  $likesCollection = $client->mini_x->likes; // Suppose que les likes sont stockés dans une collection séparée
  $commentsCollection = $client->mini_x->comments; // Suppose que les commentaires sont stockés dans une collection séparée
  $notificationsCollection = $client->mini_x->notifications;

  // Supprimer les retweets associés
  $tweetsCollection->deleteMany(['original_tweet_id' => $tweetId, 'retweet' => true]);

  // Supprimer les likes associés
  $likesCollection->deleteMany(['tweet_id' => $tweetId]);

  // Supprimer les commentaires associés
  $commentsCollection->deleteMany(['tweet_id' => $tweetId]);

  // Supprimer les notifications associées
  $notificationsCollection->deleteMany(['tweet_id' => $tweetId]);

  // Supprimer le tweet original
  $tweetsCollection->deleteOne(['_id' => $tweetId]);
}
