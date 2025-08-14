<?php
$host     = "localhost";  // Sesuaikan jika berbeda
$user     = "root";       // User database MySQL
$password = "Ra082220873747";           // Password database MySQL (kosong jika default Laragon/XAMPP)
$database = "db_akademik"; // Nama database

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
