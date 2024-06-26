<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username']) && $_SESSION['role'] === 'moderator') {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $tweetsCollection = $client->mini_x->tweets;

  $tweetId = new MongoDB\BSON\ObjectId($_POST['tweet_id']);

  // Démasquer le tweet
  $tweetsCollection->updateOne(
    ['_id' => $tweetId],
    ['$unset' => ['hidden' => '']]
  );

  $_SESSION['moderation_notification'] = "Tweet démasqué avec succès.";
  header('Location: ../doc/moderation.php');
  exit();
}
