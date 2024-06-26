<?php
session_start();
require '../doc/autoload.php';

if (!isset($_SESSION['username'])) {
  header('Location: ../doc/login.php');
  exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$notificationsCollection = $client->mini_x->notifications;

$user = $_SESSION['username'];
$notifications = $notificationsCollection->find(['user' => $user]);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notification_id'])) {
  $notificationId = new MongoDB\BSON\ObjectId($_POST['notification_id']);
  $notificationsCollection->deleteOne(['_id' => $notificationId]);
  header('Location: ../doc/notifications.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Notifications</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
  <?php include '../templates/header.php'; ?>

  <div class="d-flex flex-column min-vh-100">
    <div class="container mt-4 flex-grow-1">
      <h1 class="mb-4">Notifications</h1>

      <?php foreach ($notifications as $notification) : ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
          <?php echo htmlspecialchars($notification['message']); ?>
          <form action="../doc/notifications.php" method="POST" class="d-inline">
            <input type="hidden" name="notification_id" value="<?php echo $notification['_id']; ?>">
            <button type="submit" class="close" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

    <?php include '../templates/footer.php'; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>