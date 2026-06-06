<?php
session_start();

$dbServer = "localhost";
$dbUser   = "root";
$dbPass   = "";
$dbName   = "donorin";

try {
    $conn = new PDO(
        "mysql:host=$dbServer;dbname=$dbName;charset=utf8",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("<p style='color:red;font-family:sans-serif;'>
        Koneksi gagal: " . $e->getMessage() . "
    </p>");
}
?>