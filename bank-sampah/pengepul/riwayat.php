<?php
include '../koneksi.php';
include 'nav.html';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pengepul') {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$q_transaksi = mysqli_query($koneksi, "
    SELECT t.*, s.jenis_sampah 
    FROM transaksi t 
    JOIN sampah s ON t.id_sampah = s.id_sampah 
    WHERE t.id_user='$id_user' AND t.tipe='jual_pengepul' 
    ORDER BY t.tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembelian - Bank Sampah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="container-fluid px-3 px-md-5 my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1 text-dark">Riwayat Pembelian</h5>
                <p class="text-muted small mb-0">Daftar transaksi pengajuan dan pembelian sampah Anda.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
        <div class="card card-custom shadow-sm p-3">
            <div class="card-body">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-receipt text-primary me-2"></i>Daftar Transaksi Saya</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis Sampah</th>
                                <th>Jumlah (Kg)</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($q_transaksi) > 0) : ?>
                                <?php while ($row = mysqli_fetch_assoc($q_transaksi)) : ?>
                                <tr>
                                    <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['jenis_sampah']); ?></td>
                                    <td><?= $row['berat_kg']; ?> Kg</td>
                                    <td>Rp <?= number_format($row['total_rp'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat transaksi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>
</html>