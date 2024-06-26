<?php
session_start();
require '../doc/autoload.php';

if (!isset($_SESSION['username'])) {
  header('Location: ../doc/login.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $tweetsCollection = $client->mini_x->tweets;
  $notificationsCollection = $client->mini_x->notifications;

  $tweetId = new MongoDB\BSON\ObjectId($_POST['tweet_id']);
  $username = $_SESSION['username'];
  $action = $_POST['action'];

  // Initialiser le champ liked_by si nécessaire
  $tweetsCollection->updateOne(
    ['_id' => $tweetId, 'liked_by' => ['$exists' => false]],
    ['$set' => ['liked_by' => [], 'likes' => 0]]
  );

  if ($action == 'like') {
    likeTweet($tweetsCollection, $notificationsCollection, $tweetId, $username);
  } else {
    unlikeTweet($tweetsCollection, $tweetId, $username);
  }

  header('Location: ../doc/index.php');
  exit();
}

function likeTweet($tweetsCollection, $notificationsCollection, $tweetId, $username)
{
  $tweet = $tweetsCollection->findOne(['_id' => $tweetId]);

  if (!in_array($username, iterator_to_array($tweet['liked_by']))) {
    $tweetsCollection->updateOne(
      ['_id' => $tweetId],
      [
        '$inc' => ['likes' => 1],
        '$addToSet' => ['liked_by' => $username]
      ]
    );

    if ($tweet['user'] != $username) {
      $notificationsCollection->insertOne([
        'user' => $tweet['user'],
        'message' => "$username a aimé votre tweet.",
        'timestamp' => new MongoDB\BSON\UTCDateTime()
      ]);
    }
  }
}

function unlikeTweet($tweetsCollection, $tweetId, $username)
{
  $tweet = $tweetsCollection->findOne(['_id' => $tweetId]);

  if (in_array($username, iterator_to_array($tweet['liked_by']))) {
    $tweetsCollection->updateOne(
      ['_id' => $tweetId],
      [
        '$inc' => ['likes' => -1],
        '$pull' => ['liked_by' => $username]
      ]
    );
  }
}
