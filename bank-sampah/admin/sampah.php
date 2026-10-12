<?php
include '../koneksi.php';
include '../nav.html';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
if (isset($_POST['tambah_sampah'])) {
    $jenis_sampah = $_POST['jenis_sampah'];
    $harga_per_kg = $_POST['harga_per_kg'];
    $stok_kg      = $_POST['stok_kg'];

    mysqli_query($koneksi, "INSERT INTO sampah (jenis_sampah, harga_per_kg, stok_kg) 
                            VALUES ('$jenis_sampah', '$harga_per_kg', '$stok_kg')");
    echo "<script>alert('Data sampah berhasil ditambahkan'); location.href='sampah.php';</script>";
}
$sampah_list = mysqli_query($koneksi, "SELECT * FROM sampah");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Sampah - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #198754 0%, #0d462b 100%);
        }
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .btn-success {
            background-color: #198754;
            border: none;
            border-radius: 10px;
        }
        .btn-success:hover {
            background-color: #146c43;
        }
        .table-custom {
            border-radius: 12px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-trash-fill text-success me-2"></i>Kelola Data Sampah</h4>
                <p class="text-muted small m-0">Tambah dan lihat daftar harga serta stok sampah</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-custom bg-white p-2">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3 text-success d-flex align-items-center gap-2">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Sampah
                        </h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">Jenis Sampah</label>
                                <input type="text" name="jenis_sampah" class="form-control bg-light" placeholder="Contoh: Plastik, Botol Kaca" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">Harga/Kg (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">Rp</span>
                                    <input type="number" name="harga_per_kg" class="form-control bg-light" placeholder="0" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-secondary small fw-bold">Stok Awal (Kg)</label>
                                <input type="number" step="0.1" name="stok_kg" class="form-control bg-light" placeholder="0.0" required>
                            </div>
                            <button type="submit" name="tambah_sampah" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-custom bg-white overflow-hidden p-2">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-success text-dark">
                                    <tr>
                                        <th class="py-3 px-3">ID</th>
                                        <th class="py-3">Jenis Sampah</th>
                                        <th class="py-3">Harga / Kg</th>
                                        <th class="py-3 px-3">Stok (Kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($s = mysqli_fetch_assoc($sampah_list)) : ?>
                                    <tr>
                                        <td class="px-3 text-muted fw-bold">#<?= $s['id_sampah']; ?></td>
                                        <td class="fw-semibold text-dark"><?= $s['jenis_sampah']; ?></td>
                                        <td><span class="badge bg-success-subtle text-success px-2 py-1 fs-6">Rp <?= number_format($s['harga_per_kg']); ?></span></td>
                                        <td class="px-3 fw-medium"><?= $s['stok_kg']; ?> kg</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>