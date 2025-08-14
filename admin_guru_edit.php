<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$data_guru = null;
$error_message = "";

// --- Bagian Pengambilan Data Guru untuk Form ---
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Query untuk mengambil data guru, user, dan mapel terkait
    $stmt_select = mysqli_prepare($koneksi, "
        SELECT 
            u.id AS user_id, 
            u.nama, 
            u.email, 
            g.nip, 
            g.tempat_lahir, 
            g.tanggal_lahir, 
            g.jenis_kelamin, 
            g.alamat, 
            g.mapel_id, 
            m.nama_mapel 
        FROM guru g 
        JOIN user u ON g.user_id = u.id 
        JOIN mapel m ON g.mapel_id = m.id 
        WHERE u.id = ?
    ");
    mysqli_stmt_bind_param($stmt_select, "i", $user_id);
    mysqli_stmt_execute($stmt_select);
    $result_select = mysqli_stmt_get_result($stmt_select);

    if (mysqli_num_rows($result_select) === 1) {
        $data_guru = mysqli_fetch_assoc($result_select);
    } else {
        $error_message = "Data guru tidak ditemukan.";
        header("Location: admin_guru.php?status=error_guru_not_found");
        exit;
    }
    mysqli_stmt_close($stmt_select);
} else {
    $error_message = "ID guru tidak diberikan.";
    header("Location: admin_guru.php?status=error_id_not_given");
    exit;
}

// --- Mengambil data mata pelajaran untuk dropdown ---
$mapelResult = mysqli_query($koneksi, "SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel ASC");
if (!$mapelResult) {
    die("Query Error Mapel: " . mysqli_error($koneksi));
}

// --- Proses Update Data Guru ---
if (isset($_POST['update'])) {
    // Ambil data dari form
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $nip = $_POST['nip'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = $_POST['alamat'];
    $mapel_id = $_POST['mapel_id'];

    // Update tabel user
    $stmt_user_update = mysqli_prepare($koneksi, "UPDATE user SET nama=?, email=? WHERE id=?");
    mysqli_stmt_bind_param($stmt_user_update, "ssi", $nama, $email, $user_id);
    $update_user_success = mysqli_stmt_execute($stmt_user_update);
    mysqli_stmt_close($stmt_user_update);

    // Update tabel guru
    $stmt_guru_update = mysqli_prepare($koneksi, "UPDATE guru SET nip=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, alamat=?, mapel_id=? WHERE user_id=?");
    // Perbaikan pada baris ini. "sssssi" memiliki 6 karakter, tapi ada 7 variabel.
    // Yang benar adalah "sssssii" karena mapel_id dan user_id adalah integer.
    mysqli_stmt_bind_param($stmt_guru_update, "sssssii", $nip, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $mapel_id, $user_id);
    $update_guru_success = mysqli_stmt_execute($stmt_guru_update);
    mysqli_stmt_close($stmt_guru_update);

    if ($update_user_success && $update_guru_success) {
        header("Location: admin_guru.php?status=update_success");
        exit;
    } else {
        $error_message = "Gagal mengupdate data guru: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 700px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h3 {
            color: #0d6efd;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
        }
        .form-label {
            font-weight: 500;
            color: #343a40;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #ced4da;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }
        .btn-secondary {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .alert {
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h3>✏️ Edit Data Guru</h3>
    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($data_guru['nama']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($data_guru['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="nip" class="form-label">NIP</label>
            <input type="text" name="nip" id="nip" class="form-control" value="<?= htmlspecialchars($data_guru['nip']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
            <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control" value="<?= htmlspecialchars($data_guru['tempat_lahir']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($data_guru['tanggal_lahir']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
            <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                <option value="L" <?= ($data_guru['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="P" <?= ($data_guru['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea name="alamat" id="alamat" class="form-control" rows="3" required><?= htmlspecialchars($data_guru['alamat']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="mapel_id" class="form-label">Mata Pelajaran</label>
            <select name="mapel_id" id="mapel_id" class="form-select" required>
                <?php while ($mapel = mysqli_fetch_assoc($mapelResult)): ?>
                    <option value="<?= htmlspecialchars($mapel['id']); ?>" <?= ($mapel['id'] == $data_guru['mapel_id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($mapel['nama_mapel']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" name="update" class="btn btn-primary me-2">Update Data Guru</button>
        <a href="admin_guru.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
