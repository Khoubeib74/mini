<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username']) && $_SESSION['role'] === 'moderator') {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $tweetsCollection = $client->mini_x->tweets;

  $tweetId = new MongoDB\BSON\ObjectId($_POST['tweet_id']);

  // Marquer le tweet comme masqué
  $tweetsCollection->updateOne(
    ['_id' => $tweetId],
    ['$set' => ['hidden' => true]]
  );

  $_SESSION['notification'] = "Tweet masqué avec succès.";
  header('Location: ../doc/index.php');
  exit();
}
