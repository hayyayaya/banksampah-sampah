<?php
include '../koneksi.php';
include 'nav.html';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pengepul') {
    header("Location: ../index.php");
    exit;
}
$id_user = $_SESSION['id_user'];
$q_user  = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user='$id_user'");
$user    = mysqli_fetch_assoc($q_user);

$q_sampah = mysqli_query($koneksi, "SELECT * FROM sampah");

$q_beli = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE id_user='$id_user' AND tipe='jual_pengepul'");
$total_beli = mysqli_fetch_assoc($q_beli)['total'];

$q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE id_user='$id_user' AND tipe='jual_pengepul' AND status='pending'");
$total_pending = mysqli_fetch_assoc($q_pending)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengepul - Bank Sampah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06) !important;
        }
        .icon-box {
            font-size: 1.8rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid px-3 px-md-5 my-4">
        <div class="mb-4">
            <h5 class="fw-bold mb-1 text-dark">Layanan & Aktivitas Pengepul</h5>
            <p class="text-muted small mb-3">Akses cepat ke pembelian sampah dan riwayat transaksi Anda.</p>
            
            <div class="row g-3">
                <div class="col-md-4 col-12">
                    <div class="card card-custom shadow-sm h-100 p-2 bg-success text-white">
                        <div class="card-body">
                            <p class="text-white-50 small mb-2">Saldo Pengepul</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <i class="bi bi-wallet2 text-white icon-box"></i>
                                <span class="fw-bold fs-4 text-white">Rp <?= number_format($user['saldo'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-6">
                    <a href="beli.php" class="text-decoration-none">
                        <div class="card card-custom shadow-sm h-100 p-2">
                            <div class="card-body">
                                <p class="text-muted small mb-2">Beli Sampah</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <i class="bi bi-cart-plus text-success icon-box"></i>
                                    <span class="fw-bold fs-4 text-dark">+ Transaksi</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 col-6">
                    <a href="riwayat.php" class="text-decoration-none">
                        <div class="card card-custom shadow-sm h-100 p-2">
                            <div class="card-body">
                                <p class="text-muted small mb-2">Riwayat Pembelian</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <i class="bi bi-receipt text-primary icon-box"></i>
                                    <span class="fw-bold fs-4 text-dark"><?= $total_beli; ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <h5 class="fw-bold mb-3 text-dark">Status & Stok Sampah Tersedia</h5>
            <div class="card card-custom shadow-sm p-3 mb-4">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-12">
                            <p class="text-muted small mb-1">Status Pengajuan Pembelian</p>
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-arrow-repeat text-warning fs-2"></i>
                                <div>
                                    <span class="fw-bold fs-4 text-dark"><?= $total_pending; ?> Pengajuan</span>
                                    <div class="text-muted small">Menunggu Konfirmasi Admin</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom shadow-sm p-3">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-box-seam text-success me-2"></i>Daftar Stok Sampah Siap Dibeli</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Jenis Sampah</th>
                                    <th>Harga / Kg</th>
                                    <th>Stok Tersedia (Kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($q_sampah)) : ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['jenis_sampah']); ?></td>
                                    <td>Rp <?= number_format($row['harga_per_kg'], 0, ',', '.'); ?></td>
                                    <td><span class="badge bg-info text-dark fs-6"><?= $row['stok_kg']; ?> Kg</span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>