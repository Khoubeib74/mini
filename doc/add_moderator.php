add_moderator.php:

<?php
require '../doc/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->mini_x->users;

$username = "moderatorUser";
$password = password_hash("moderatorPassword", PASSWORD_BCRYPT);
$role = "moderator";

$collection->insertOne([
  'username' => $username,
  'password' => $password,
  'role' => $role
]);

echo "Modérateur ajouté avec succès!";
?>