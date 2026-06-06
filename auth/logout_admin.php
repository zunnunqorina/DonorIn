<?php
include 'koneksi.php';

unset($_SESSION['admin_login']);
unset($_SESSION['admin_username']);

session_destroy();

header("Location: login_admin.php");
exit;
?>
