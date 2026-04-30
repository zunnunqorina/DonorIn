<?php
$dbServer = "localhost";
$dbUser   = "root";
$dbPass   = "";      
$dbName   = "donorin";

$conn = mysqli_connect($dbServer, $dbUser, $dbPass, $dbName);

if (!$conn) {
    die("<p style='color:red;font-family:sans-serif;'>
        Koneksi gagal: " . mysqli_connect_error() . "
    </p>");
}
mysqli_set_charset($conn, "utf8");
?>