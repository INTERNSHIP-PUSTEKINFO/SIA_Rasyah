<?php
session_start();
include 'koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Cek apakah ada ID di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect ke halaman daftar kelas dengan pesan gagal jika ID tidak ada
    header("Location: admin_kelas.php?msg=gagal_hapus_invalid_id");
    exit;
}

$id = $_GET['id'];

// Proses hapus kelas berdasarkan ID menggunakan prepared statement
$stmt = mysqli_prepare($koneksi, "DELETE FROM kelas WHERE id = ?");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    $berhasil = mysqli_stmt_execute($stmt);
    
    // Cek apakah eksekusi berhasil dan ada baris yang terpengaruh
    if ($berhasil && mysqli_stmt_affected_rows($stmt) > 0) {
        // Redirect ke halaman daftar kelas dengan pesan sukses
        header("Location: admin_kelas.php?msg=delete_success");
        exit;
    } else {
        // Redirect jika eksekusi berhasil tapi tidak ada baris yang dihapus
        header("Location: admin_kelas.php?msg=gagal_hapus_not_found");
        exit;
    }
} else {
    // Redirect jika prepared statement gagal dibuat
    header("Location: admin_kelas.php?msg=gagal_query");
    exit;
}
?>