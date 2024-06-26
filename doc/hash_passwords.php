hash_passwords.php:

<?php
require '../doc/autoload.php';

// Connect to MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->mini_x->users;

// Retrieve all users
$users = $collection->find();

foreach ($users as $user) {
  // Check if the password is already hashed (assuming hashed passwords are at least 60 characters long)
  if (strlen($user['password']) < 60) {
    $hashedPassword = password_hash($user['password'], PASSWORD_BCRYPT);
    $collection->updateOne(
      ['_id' => $user['_id']],
      ['$set' => ['password' => $hashedPassword]]
    );
  }
}

echo "Les mots de passe des utilisateurs ont été hachés.";
?>