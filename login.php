<?php
include 'koneksi.php';
session_start();

$error = "";

// Jika sudah login, langsung redirect sesuai role
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'guru') {
        header("Location: dashboard_guru.php");
        exit;
    } elseif ($_SESSION['user']['role'] === 'siswa') {
        header("Location: dashboard_siswa.php");
        exit;
    } elseif ($_SESSION['user']['role'] === 'admin') {
        header("Location: dashboard_admin.php");
        exit;
    }
}

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM user WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $data = mysqli_fetch_assoc($result);

        // Verifikasi password
        if (password_verify($password, $data['password'])) {
            $_SESSION['user'] = [
                'id'    => $data['id'],
                'email' => $data['email'],
                'nama'  => $data['nama'],
                'role'  => $data['role']
            ];

            // Redirect sesuai role
            if ($data['role'] === 'guru') {
                header("Location: dashboard_guru.php");
            } elseif ($data['role'] === 'siswa') {
                header("Location: dashboard_siswa.php");
            } elseif ($data['role'] === 'admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "⚠️ Password salah!";
        }
    } else {
        $error = "⚠️ Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Akademik SMK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #007bff, #00c6ff);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }
        .login-box {
            width: 100%;
            max-width: 420px;
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .login-box h2 {
            font-weight: 700;
            margin-bottom: 30px;
            color: #007bff;
            text-align: center;
        }
        .form-label {
            font-weight: 500;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            border-color: #007bff;
        }
        .btn-primary {
            background: #007bff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 12px;
            transition: background 0.3s ease;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .alert {
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login Akademik SMK</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan email Anda" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">Login</button>
        </form>
    </div>
</body>
</html>