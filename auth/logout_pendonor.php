<?php
include '../config/koneksi.php';

unset($_SESSION['pendonor_login']);
unset($_SESSION['pendonor_id']);
unset($_SESSION['pendonor_nama']);
unset($_SESSION['pendonor_goldar']);

$conn = null;
header("Location: ../login.php");
exit;
?>