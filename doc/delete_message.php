delete_message.php:

<?php
session_start();
require '../doc/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['username']) && $_SESSION['role'] === 'moderator') {
  $client = new MongoDB\Client("mongodb://localhost:27017");
  $messagesCollection = $client->mini_x->messages;

  $messageId = new MongoDB\BSON\ObjectId($_POST['message_id']);

  // Supprimer le message
  $messagesCollection->deleteOne(['_id' => $messageId]);

  $_SESSION['notification'] = "Message supprimé avec succès.";
  header('Location: ../doc/messages.php');
  exit();
}
?>