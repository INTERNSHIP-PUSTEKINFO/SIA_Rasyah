<?php
session_start();
include 'koneksi.php';

// Periksa apakah user adalah admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Periksa apakah ada ID yang dikirim melalui URL
if (!isset($_GET['id'])) {
    header("Location: admin_user.php");
    exit;
}

$id = $_GET['id'];

// Mengambil peran (role) user yang akan dihapus
// Menggunakan prepared statement untuk keamanan
$stmt_get_role = $koneksi->prepare("SELECT role FROM user WHERE id = ?");
$stmt_get_role->bind_param("i", $id);
$stmt_get_role->execute();
$result_role = $stmt_get_role->get_result();

if ($result_role->num_rows === 0) {
    // User tidak ditemukan, arahkan kembali dengan pesan error
    header("Location: admin_user.php?pesan=hapus_gagal&error=user_not_found");
    exit;
}

$user_data = $result_role->fetch_assoc();
$user_role = $user_data['role'];

// Memulai transaksi untuk memastikan semua penghapusan berhasil atau tidak sama sekali
mysqli_begin_transaction($koneksi);

try {
    // Hapus data terkait di tabel 'guru' atau 'siswa' berdasarkan peran
    if ($user_role === 'guru') {
        $stmt_delete_guru = $koneksi->prepare("DELETE FROM guru WHERE user_id = ?");
        $stmt_delete_guru->bind_param("i", $id);
        if (!$stmt_delete_guru->execute()) {
            throw new Exception("Gagal menghapus data guru.");
        }
    } elseif ($user_role === 'siswa') {
        $stmt_delete_siswa = $koneksi->prepare("DELETE FROM siswa WHERE user_id = ?");
        $stmt_delete_siswa->bind_param("i", $id);
        if (!$stmt_delete_siswa->execute()) {
            throw new Exception("Gagal menghapus data siswa.");
        }
    }

    // Setelah data terkait dihapus, hapus data user utama
    $stmt_delete_user = $koneksi->prepare("DELETE FROM user WHERE id = ?");
    $stmt_delete_user->bind_param("i", $id);
    if (!$stmt_delete_user->execute()) {
        throw new Exception("Gagal menghapus data user.");
    }

    // Jika semua query berhasil, commit transaksi
    mysqli_commit($koneksi);
    
    // Arahkan kembali dengan pesan sukses
    header("Location: admin_user.php?pesan=hapus_berhasil");
    exit;

} catch (Exception $e) {
    // Jika ada error, rollback transaksi dan arahkan kembali dengan pesan error
    mysqli_rollback($koneksi);
    
    header("Location: admin_user.php?pesan=hapus_gagal&error=" . urlencode($e->getMessage()));
    exit;
}

// Tutup koneksi database
mysqli_close($koneksi);
?>
