<?php
session_start();

// Jika user belum login, arahkan ke halaman login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Ambil role user yang sedang login
$role = $_SESSION['user']['role'];

// Redirect berdasarkan role
switch ($role) {
    case 'admin':
        header("Location: dashboard_Admin.php"); // (bisa kamu buat nanti)
        break;
    case 'guru':
        header("Location: dashboard_Guru.php");
        break;
    case 'siswa':
        header("Location: dashboard_Siswa.php");
        break;
    default:
        echo "Role tidak dikenali. Silakan login ulang.";
        session_destroy();
        exit;
}
