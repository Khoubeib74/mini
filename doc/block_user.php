<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username']) && $_SESSION['role'] === 'moderator') {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $usersCollection = $client->mini_x->users;

  $username = $_POST['username'];
  $action = $_POST['action'];

  if ($username === $_SESSION['username']) {
    $_SESSION['notification'] = "Vous ne pouvez pas vous bloquer vous-même.";
  } else {
    if ($action == 'block') {
      $usersCollection->updateOne(['username' => $username], ['$set' => ['blocked' => true]]);
      $_SESSION['notification'] = "Utilisateur $username bloqué avec succès.";
    } elseif ($action == 'unblock') {
      $usersCollection->updateOne(['username' => $username], ['$unset' => ['blocked' => '']]);
      $_SESSION['notification'] = "Utilisateur $username débloqué avec succès.";
    }
  }

  header('Location: ../doc/index.php');
  exit();
}
