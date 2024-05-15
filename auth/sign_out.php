<?php
session_start();
unset($_SESSION['user_login']);
unset($_SESSION['admin_login']);
unset($_SESSION['staff_login']);
header('location:../home.php');
?>