<?php
session_start(); // Memulai sesi PHP
include 'koneksi.php'; // Mengimpor file koneksi database

// --- Kontrol Akses ---
// Memastikan hanya pengguna dengan peran 'admin' yang dapat mengakses halaman ini.
// Jika tidak, pengguna akan diarahkan ke halaman login.
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit; // Menghentikan eksekusi skrip setelah redirect
}

// --- Proses Penghapusan Data Guru ---
// Memeriksa apakah parameter 'id' (user_id) ada di URL.
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Mengambil user_id dan memastikan nilainya adalah integer untuk keamanan.

    // --- Pencegahan SQL Injection dengan Prepared Statement ---
    // Menghapus data dari tabel 'user'. Karena ada ON DELETE CASCADE,
    // data guru yang terkait di tabel 'guru' juga akan otomatis terhapus.
    $stmt_delete = mysqli_prepare($koneksi, "DELETE FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt_delete, "i", $user_id); // Mengikat user_id sebagai integer

    // Menjalankan prepared statement.
    if (mysqli_stmt_execute($stmt_delete)) {
        // --- Penanganan Berhasil ---
        // Jika penghapusan berhasil, pengguna diarahkan kembali ke halaman daftar guru
        // dengan parameter 'status=delete_success' untuk memberikan umpan balik.
        header("Location: admin_guru.php?status=delete_success");
        exit;
    } else {
        // --- Penanganan Gagal ---
        // Jika terjadi kesalahan saat eksekusi (misalnya, masalah database),
        // pengguna diarahkan dengan parameter 'status=delete_error'.
        header("Location: admin_guru.php?status=delete_error&message=" . urlencode(mysqli_error($koneksi)));
        exit;
    }

    mysqli_stmt_close($stmt_delete); // Menutup statement
} else {
    // --- Penanganan ID Tidak Ditemukan ---
    // Jika parameter 'id' tidak ada di URL, pengguna diarahkan kembali
    // dengan parameter 'status=error_id_not_found'.
    header("Location: admin_guru.php?status=error_id_not_found");
    exit;
}
?>