<?php
session_start();

if (isset($_POST['notification_type'])) {
  $notificationType = $_POST['notification_type'];
  unset($_SESSION[$notificationType]);
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
