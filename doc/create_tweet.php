create_tweet.php:

<?php
session_start();
require '../doc/autoload.php';

if (!isset($_SESSION['username'])) {
  header('Location: ../doc/login.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $collection = $client->mini_x->tweets;

  $user = $_SESSION['username'];
  $message = $_POST['message'];

  $collection->insertOne([
    'user' => $user,
    'message' => $message,
    'timestamp' => new MongoDB\BSON\UTCDateTime(),
    'likes' => 0,
    'comments' => []
  ]);

  $_SESSION['notification'] = "Votre tweet a été publié avec succès!";
  header('Location: ../doc/index.php');
}
?>