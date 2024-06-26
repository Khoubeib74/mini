<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username']) && $_SESSION['role'] === 'moderator') {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $usersCollection = $client->mini_x->users;

  $username = $_POST['username'];

  // Débloquer l'utilisateur
  $usersCollection->updateOne(
    ['username' => $username],
    ['$unset' => ['blocked' => '']]
  );

  $_SESSION['moderation_notification'] = "Utilisateur $username débloqué avec succès.";
  header('Location: ../doc/moderation.php');
  exit();
}
