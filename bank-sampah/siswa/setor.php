<?php
include '../koneksi.php';
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'siswa')) {
    header("Location: ../index.php");
    exit;
}
$id_user = $_SESSION['id_user'];
if (isset($_POST['setor'])) {
    $id_sampah = $_POST['id_sampah'];
    $berat_kg  = floatval($_POST['berat_kg']);
    $tanggal   = date('Y-m-d H:i:s');

    $q_sampah = mysqli_query($koneksi, "SELECT harga_per_kg FROM sampah WHERE id_sampah = '$id_sampah'");
    $d_sampah = mysqli_fetch_assoc($q_sampah);
    $total_rp = $berat_kg * $d_sampah['harga_per_kg'];

    mysqli_query($koneksi, "INSERT INTO transaksi (id_user, id_sampah, tipe, berat_kg, total_rp, tanggal, status) 
                            VALUES ('$id_user', '$id_sampah', 'setor', '$berat_kg', '$total_rp', '$tanggal', 'pending')");

    echo "<script>alert('Setoran berhasil diajukan, menunggu persetujuan admin.'); location.href='riwayat_setor.php';</script>";
}
$sampah_list = mysqli_query($koneksi, "SELECT * FROM sampah");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor Sampah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .card-custom { border: none; border-radius: 16px; }
        .navbar-custom { background: linear-gradient(135deg, #198754 0%, #0d462b 100%) !important; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark navbar-custom py-3 shadow-sm mb-4">
        <div class="container-fluid px-3 px-md-5">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2 m-0 text-white" href="index.php">
                <i class="bi bi-recycle fs-4"></i> Bank Sampah
            </a>
            <div class="d-flex gap-2 ms-auto">
                <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
                <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')" class="btn btn-outline-light btn-sm px-3 rounded-pill">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-3 px-md-5 my-4">
        <div class="card card-custom shadow-sm p-4 bg-white">
            <h5 class="fw-bold mb-4 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-box-arrow-in-down text-success"></i> Form Setor Sampah
            </h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">Jenis Sampah</label>
                    <select name="id_sampah" class="form-select form-select-lg" required>
                        <?php while ($s = mysqli_fetch_assoc($sampah_list)) : ?>
                            <option value="<?= $s['id_sampah']; ?>"><?= htmlspecialchars($s['jenis_sampah']); ?> (Rp<?= number_format($s['harga_per_kg'], 0, ',', '.'); ?>/kg)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small fw-semibold">Berat (Kg)</label>
                    <input type="number" step="0.1" min="0.1" name="berat_kg" class="form-control form-control-lg" placeholder="Masukkan berat..." required>
                </div>
                <button type="submit" name="setor" class="btn btn-success w-100 fw-bold py-2 rounded-3 mb-2">
                    Submit Setoran
                </button>
                <a href="riwayat_setor.php" class="btn btn-outline-secondary w-100 py-2 rounded-3 text-decoration-none text-center">
                    Lihat Riwayat Setor
                </a>
            </form>
        </div>
    </div>
</body>
</html>