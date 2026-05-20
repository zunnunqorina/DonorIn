<?php
// ============================================================
//  Cek Session Admin
//  Include file ini di setiap halaman yang membutuhkan login
//  Materi: isset($_SESSION), redirect jika belum login
// ============================================================

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login_admin.php");
    exit;
}
?>
