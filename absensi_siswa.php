<?php
// Memulai sesi untuk mengelola data pengguna.
session_start();
// Menyertakan file koneksi database.
include 'koneksi.php';

// Validasi role, hanya siswa yang bisa mengakses halaman ini.
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: login.php");
    exit;
}

// Ambil user_id dan nama dari sesi.
$user_id = $_SESSION['user']['id'];
$nama_siswa = $_SESSION['user']['nama'];
$siswa_nis = '';

// Cari NIS dari tabel siswa berdasarkan user_id.
$nis_query = "SELECT nis FROM siswa WHERE user_id = ?";
if ($nis_stmt = mysqli_prepare($koneksi, $nis_query)) {
    mysqli_stmt_bind_param($nis_stmt, "i", $user_id);
    mysqli_stmt_execute($nis_stmt);
    $nis_result = mysqli_stmt_get_result($nis_stmt);
    if ($nis_data = mysqli_fetch_assoc($nis_result)) {
        $siswa_nis = $nis_data['nis'];
    }
    mysqli_stmt_close($nis_stmt);
}

// Jika NIS tidak ditemukan, hentikan eksekusi dan tampilkan pesan.
if (empty($siswa_nis)) {
    die("Data NIS siswa tidak ditemukan. Silakan hubungi admin.");
}

// Ambil data mata pelajaran untuk dropdown formulir.
$query_mapel = "SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel ASC";
$result_mapel = mysqli_query($koneksi, $query_mapel);

// Tampilkan pesan status
$status_message = '';
$alert_type = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $status_message = "Absensi berhasil disimpan.";
        $alert_type = 'success';
    } elseif ($_GET['status'] === 'error') {
        $status_message = "Gagal menyimpan absensi.";
        $alert_type = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Absensi Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Poppins', sans-serif; }
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
            padding: 20px;
            margin-bottom: 30px;
        }
        .btn-primary { background-color: #007bff; border-color: #007bff; border-radius: 8px; font-weight: 500; }
        .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; border-color: #6c757d; border-radius: 8px; font-weight: 500; }
        .btn-secondary:hover { background-color: #5a6268; border-color: #5a6268; }
        .form-control, .form-select { border-radius: 8px; }
        h3 { font-weight: 700; color: #2c3e50; border-left: 5px solid #007bff; padding-left: 15px; margin-bottom: 25px; }
    </style>
</head>
<body>

<div class="container mt-5">
    <h3><i class="fas fa-edit me-2"></i> Formulir Absensi</h3>
    <div class="card p-4">
        <?php if ($status_message): ?>
            <div class="alert alert-<?= htmlspecialchars($alert_type) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($status_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <form action="proses_absensi.php" method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="mb-3">
                <label for="siswa_nis" class="form-label">Nama Siswa</label>
                <input type="text" class="form-control" id="nama_siswa" value="<?= htmlspecialchars($nama_siswa) ?> (<?= htmlspecialchars($siswa_nis) ?>)" disabled>
                <input type="hidden" name="siswa_nis" value="<?= htmlspecialchars($siswa_nis) ?>">
            </div>
            <div class="mb-3">
                <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                <select class="form-select" id="mapel_id" name="mapel_id" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php while ($row = mysqli_fetch_assoc($result_mapel)): ?>
                        <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['nama_mapel']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <select class="form-select" id="keterangan" name="keterangan" required>
                    <option value="">-- Pilih Keterangan --</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Absensi</button>
                <a href="dashboard_siswa.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
