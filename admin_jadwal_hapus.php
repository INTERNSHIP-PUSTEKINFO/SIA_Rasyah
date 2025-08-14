<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Pastikan ada parameter id yang dikirim
if (!isset($_GET['id'])) {
    header("Location: admin_jadwal.php");
    exit;
}

$id = $_GET['id'];

// Hapus data jadwal dari database
$query = "DELETE FROM jadwal_pelajaran WHERE id = '$id'";
if (mysqli_query($koneksi, $query)) {
    header("Location: admin_jadwal.php");
    exit;
} else {
    echo "<script>alert('Gagal menghapus jadwal.'); window.location='admin_jadwal.php';</script>";
}
?>
