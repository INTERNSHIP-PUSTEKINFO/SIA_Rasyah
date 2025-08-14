<?php
session_start();
include 'koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Validasi ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_jurusan.php");
    exit;
}

$id = $_GET['id'];

// Proses hapus jurusan
$stmt = mysqli_prepare($koneksi, "DELETE FROM jurusan WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

// Cek apakah baris terhapus
if (mysqli_stmt_affected_rows($stmt) > 0) {
    header("Location: admin_jurusan.php?msg=berhasil");
    exit;
} else {
    // Jurusan tidak ditemukan atau gagal dihapus
    header("Location: admin_jurusan.php?msg=gagal");
    exit;
}
?>
