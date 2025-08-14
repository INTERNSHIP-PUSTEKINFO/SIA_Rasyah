<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_absensi.php");
    exit;
}

$id = $_GET['id'];

// Cek apakah data absensi ada
$cek = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id = '$id'");
if (mysqli_num_rows($cek) === 0) {
    header("Location: admin_absensi.php?pesan=notfound");
    exit;
}

// Lakukan penghapusan
$hapus = mysqli_query($koneksi, "DELETE FROM absensi WHERE id = '$id'");
if ($hapus) {
    header("Location: admin_absensi.php?pesan=sukses");
} else {
    header("Location: admin_absensi.php?pesan=gagal");
}
exit;
?>
