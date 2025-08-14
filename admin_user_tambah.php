<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password']; // Ambil password tanpa hash dulu
    $role = $_POST['role'];

    $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        // Hash password sebelum disimpan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO user (nama, email, password, role) VALUES ('$nama', '$email', '$hashed_password', '$role')";
        if (mysqli_query($koneksi, $query)) {
            header("Location: admin_user.php?success=tambah");
            exit;
        } else {
            $error = "Gagal menambahkan user: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Poppins', sans-serif; }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-bottom: 3px solid #007bff;
        }
        .navbar .navbar-brand { font-weight: 700; color: #007bff !important; font-size: 1.5rem; }
        .navbar .nav-link { color: #555 !important; font-weight: 500; margin-right: 15px; transition: color 0.3s ease-in-out; }
        .navbar .nav-link:hover { color: #007bff !important; }
        .container { max-width: 800px; }
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
            padding: 30px;
        }
        h3 {
            font-weight: 700;
            color: #2c3e50;
            border-left: 5px solid #007bff;
            padding-left: 15px;
            margin-bottom: 25px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            border-color: #007bff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard_admin.php">Akademik SMK</a>
        <div class="d-flex align-items-center">
            <a class="nav-link" href="dashboard_admin.php">Dashboard</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3><i class="fas fa-user-plus me-2"></i>Tambah User</h3>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password" minlength="4" placeholder="Minimal 4 karakter" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="siswa">Siswa</option>
                </select>
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-1"></i> Simpan</button>
                <a href="admin_user.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
