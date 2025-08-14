<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login dan memiliki role yang diizinkan untuk mengelola absensi
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['user']['role'];
$action = $_REQUEST['action'] ?? '';

// Cek hak akses untuk setiap aksi
switch ($action) {
    case 'tambah':
        // Aksi tambah absensi
        if ($role === 'admin' || $role === 'siswa') {
            $siswa_nis = trim($_POST['siswa_nis']);
            $mapel_id = trim($_POST['mapel_id']);
            $tanggal = trim($_POST['tanggal']);
            $keterangan = trim($_POST['keterangan']);

            if (empty($siswa_nis) || empty($mapel_id) || empty($tanggal) || empty($keterangan)) {
                header("Location: absensi_siswa.php?status=error");
                exit;
            }

            $query = "INSERT INTO absensi (siswa_nis, mapel_id, tanggal, keterangan) VALUES (?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($koneksi, $query)) {
                mysqli_stmt_bind_param($stmt, "siss", $siswa_nis, $mapel_id, $tanggal, $keterangan);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: absensi_siswa.php?status=success");
                    exit;
                } else {
                    header("Location: absensi_siswa.php?status=error");
                    exit;
                }
                mysqli_stmt_close($stmt);
            } else {
                header("Location: absensi_siswa.php?status=error");
                exit;
            }
        } else {
            header("Location: dashboard.php?error=unauthorized");
            exit;
        }
        break;

    case 'edit':
        // Aksi edit absensi
        if ($role === 'admin') {
            $id = $_POST['id'];
            $tanggal = trim($_POST['tanggal']);
            $keterangan = trim($_POST['keterangan']);

            if (empty($id) || empty($tanggal) || empty($keterangan)) {
                header("Location: absensi_admin_guru.php?status=error");
                exit;
            }

            $query = "UPDATE absensi SET tanggal = ?, keterangan = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($koneksi, $query)) {
                mysqli_stmt_bind_param($stmt, "ssi", $tanggal, $keterangan, $id);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: absensi_admin_guru.php?status=success");
                    exit;
                } else {
                    header("Location: absensi_admin_guru.php?status=error");
                    exit;
                }
                mysqli_stmt_close($stmt);
            } else {
                header("Location: absensi_admin_guru.php?status=error");
                exit;
            }
        } else {
            header("Location: dashboard.php?error=unauthorized");
            exit;
        }
        break;

    case 'hapus':
        // Aksi hapus absensi
        if ($role === 'admin') {
            $id = $_GET['id'];

            if (empty($id)) {
                header("Location: absensi_admin_guru.php?status=error");
                exit;
            }

            $query = "DELETE FROM absensi WHERE id = ?";
            if ($stmt = mysqli_prepare($koneksi, $query)) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: absensi_admin_guru.php?status=deleted");
                    exit;
                } else {
                    header("Location: absensi_admin_guru.php?status=error");
                    exit;
                }
                mysqli_stmt_close($stmt);
            } else {
                header("Location: absensi_admin_guru.php?status=error");
                exit;
            }
        } else {
            header("Location: dashboard.php?error=unauthorized");
            exit;
        }
        break;

    default:
        // Jika tidak ada aksi yang valid, arahkan ke dashboard
        header("Location: dashboard.php");
        exit;
}
?>
