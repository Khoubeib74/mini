<?php
session_start();
session_destroy();
header('Location: ../doc/login.php');
