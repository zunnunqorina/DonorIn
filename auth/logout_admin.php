<?php
include '../config/koneksi.php';

unset($_SESSION['admin_login']);
unset($_SESSION['admin_username']);

$conn = null;
session_destroy();

header("Location: ../login.php");
exit;
?>