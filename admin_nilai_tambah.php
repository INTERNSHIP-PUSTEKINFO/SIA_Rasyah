<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil data siswa dan mapel
$siswaResult = mysqli_query($koneksi, "SELECT s.nis, u.nama FROM siswa s JOIN user u ON s.user_id = u.id ORDER BY u.nama ASC");
$mapelResult = mysqli_query($koneksi, "SELECT * FROM mapel ORDER BY nama_mapel ASC");

if (isset($_POST['simpan'])) {
    $siswa_nis = $_POST['siswa_nis'];
    $mapel_id = $_POST['mapel_id'];
    $nilai_tugas = $_POST['nilai_tugas'];
    $nilai_uts = $_POST['nilai_uts'];
    $nilai_uas = $_POST['nilai_uas'];
    $semester = $_POST['semester'];
    $tahun_ajaran = $_POST['tahun_ajaran'];

    // Hitung nilai akhir
    $nilai_akhir = round(($nilai_tugas + $nilai_uts + $nilai_uas) / 3, 2);

    // Simpan ke DB
    $query = "INSERT INTO nilai (siswa_nis, mapel_id, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir, semester, tahun_ajaran)
              VALUES ('$siswa_nis', '$mapel_id', '$nilai_tugas', '$nilai_uts', '$nilai_uas', '$nilai_akhir', '$semester', '$tahun_ajaran')";
    $simpan = mysqli_query($koneksi, $query);

    if ($simpan) {
        header("Location: admin_nilai.php?msg=berhasil");
        exit;
    } else {
        echo "Gagal menyimpan nilai: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Nilai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>➕ Tambah Nilai Siswa</h3>
    <form method="post">
        <div class="mb-3">
            <label for="siswa_nis" class="form-label">Nama Siswa</label>
            <select name="siswa_nis" class="form-select" required>
                <option value="">-- Pilih Siswa --</option>
                <?php while ($s = mysqli_fetch_assoc($siswaResult)): ?>
                    <option value="<?= $s['nis']; ?>"><?= $s['nama']; ?> (<?= $s['nis']; ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="mapel_id" class="form-label">Mata Pelajaran</label>
            <select name="mapel_id" class="form-select" required>
                <option value="">-- Pilih Mapel --</option>
                <?php while ($m = mysqli_fetch_assoc($mapelResult)): ?>
                    <option value="<?= $m['id']; ?>"><?= $m['nama_mapel']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Nilai Tugas</label>
                <input type="number" step="0.01" name="nilai_tugas" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label>Nilai UTS</label>
                <input type="number" step="0.01" name="nilai_uts" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label>Nilai UAS</label>
                <input type="number" step="0.01" name="nilai_uas" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Semester</label>
                <select name="semester" class="form-select" required>
                    <option value="">-- Pilih Semester --</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" class="form-control" placeholder="Contoh: 2024/2025" required>
            </div>
        </div>

        <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
        <a href="admin_nilai.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
</body>
</html>
