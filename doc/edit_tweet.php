edit_tweet.php:

<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username'])) {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $collection = $client->mini_x->tweets;

  $tweetId = new MongoDB\BSON\ObjectId($_POST['tweet_id']);
  $newMessage = $_POST['new_message'];
  $user = $_SESSION['username'];

  $tweet = $collection->findOne(['_id' => $tweetId]);

  if ($tweet['user'] === $user) {
    $collection->updateOne(
      ['_id' => $tweetId],
      ['$set' => ['message' => $newMessage]]
    );
    $_SESSION['notification'] = "Votre tweet a été modifié avec succès!";
  } else {
    $_SESSION['notification'] = "Vous n'êtes pas autorisé à modifier ce tweet.";
  }

  header('Location: ../doc/index.php');
}
?>