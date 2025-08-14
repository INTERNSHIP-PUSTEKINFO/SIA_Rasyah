<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Cek apakah data nilai dengan ID ini ada
    $cek = mysqli_query($koneksi, "SELECT * FROM nilai WHERE id = $id");
    if (mysqli_num_rows($cek) > 0) {
        // Hapus nilai
        $hapus = mysqli_query($koneksi, "DELETE FROM nilai WHERE id = $id");

        if ($hapus) {
            header("Location: admin_nilai.php?msg=hapus");
            exit;
        } else {
            echo "Gagal menghapus nilai: " . mysqli_error($koneksi);
        }
    } else {
        echo "Data nilai tidak ditemukan.";
    }
} else {
    header("Location: admin_nilai.php");
    exit;
}
?>
