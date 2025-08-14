<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nis = $_GET['nis'] ?? null;
if ($nis) {
    $query = mysqli_query($koneksi, "SELECT user_id FROM siswa WHERE nis='$nis'");
    $siswa = mysqli_fetch_assoc($query);

    mysqli_query($koneksi, "DELETE FROM siswa WHERE nis='$nis'");
    if ($siswa) {
        mysqli_query($koneksi, "DELETE FROM user WHERE id='{$siswa['user_id']}'");
    }
}

header("Location: admin_siswa.php");
exit;
