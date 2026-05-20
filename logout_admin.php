<?php
// ============================================================
//  Logout Admin — menghapus SESSION
//  Materi: unset($_SESSION), session_destroy()
// ============================================================

include 'koneksi.php';

// Hapus variabel session admin
unset($_SESSION['admin_login']);
unset($_SESSION['admin_username']);

// Hancurkan seluruh session
session_destroy();

// Redirect ke halaman login
header("Location: login_admin.php");
exit;
?>
