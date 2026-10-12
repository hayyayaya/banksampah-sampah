<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bank-sampah";
$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
session_start();
?>