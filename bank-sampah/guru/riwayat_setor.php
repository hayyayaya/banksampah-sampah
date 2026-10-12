<?php
include '../koneksi.php';
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'guru')) {
    header("Location: ../index.php");
    exit;
}
$id_user = $_SESSION['id_user'];
$q_per_jenis = mysqli_query($koneksi, "SELECT s.jenis_sampah, SUM(t.berat_kg) as total_berat 
                                       FROM transaksi t 
                                       JOIN sampah s ON t.id_sampah = s.id_sampah 
                                       WHERE t.id_user = '$id_user' AND t.tipe = 'setor' AND (t.status = 'selesai' OR t.status = 'berhasil')
                                       GROUP BY t.id_sampah");
$query = "SELECT t.*, s.jenis_sampah 
          FROM transaksi t 
          JOIN sampah s ON t.id_sampah = s.id_sampah 
          WHERE t.id_user = '$id_user' AND t.tipe = 'setor' 
          ORDER BY t.tanggal DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Setor Sampah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .blue-card {
            background: linear-gradient(135deg, #2b77e5 0%, #1a52c9 100%);
            border-radius: 16px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }
        .blue-card::after {
            content: "";
            position: absolute;
            right: -20px;
            top: -20px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            pointer-events: none;
        }
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
        
        <div class="card card-custom shadow-sm p-3 mb-4 bg-white">
            <div class="blue-card p-4 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-white-50 small fw-semibold">Total Sampah Disetujui</span>
                    <i class="bi bi-trash3 fs-4 text-white-50"></i>
                </div>
                
                <div class="row g-2">
                    <?php if (mysqli_num_rows($q_per_jenis) > 0): ?>
                        <?php while ($j = mysqli_fetch_assoc($q_per_jenis)): ?>
                            <div class="col-6">
                                <div class="bg-white bg-opacity-10 p-2 rounded-3">
                                    <div class="text-white-50 small" style="font-size: 0.75rem;"><?= htmlspecialchars($j['jenis_sampah']); ?></div>
                                    <div class="fw-bold fs-5"><?= number_format($j['total_berat'], 1, ',', '.'); ?> <span class="fs-6 fw-normal">Kg</span></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-white-50 small">Belum ada sampah disetor yang disetujui.</div>
                    <?php endif; ?>
                </div>
            </div>

            <a href="setor.php" class="btn btn-outline-primary w-100 fw-bold py-2 rounded-3 text-decoration-none text-center">
                + Setor Sampah Baru
            </a>
        </div>

        <h5 class="fw-bold text-dark mb-3">Riwayat Setor</h5>

        <div class="card card-custom shadow-sm p-2">
            <div class="card-body p-0">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php while ($row = mysqli_fetch_assoc($result)) : 
                            $status = isset($row['status']) ? $row['status'] : 'pending';
                        ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-success-subtle text-success p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-arrow-down-left-circle fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($row['jenis_sampah']); ?></h6>
                                        <div class="text-muted small">
                                            <?= date('d M Y, H:i', strtotime($row['tanggal'])); ?> • <span class="badge bg-light text-dark border"><?= $row['berat_kg']; ?> Kg</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?php if ($status == 'selesai' || $status == 'berhasil') : ?>
                                        <span class="fw-bold text-success fs-6">+Rp <?= number_format($row['total_rp'], 0, ',', '.'); ?></span>
                                        <div class="mt-1"><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Selesai</span></div>
                                    <?php elseif ($status == 'ditolak') : ?>
                                        <span class="fw-bold text-muted fs-6" style="text-decoration: line-through;">Rp <?= number_format($row['total_rp'], 0, ',', '.'); ?></span>
                                        <div class="mt-1"><span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span></div>
                                    <?php else : ?>
                                        <span class="fw-bold text-muted fs-6">+Rp <?= number_format($row['total_rp'], 0, ',', '.'); ?></span>
                                        <div class="mt-1"><span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Menunggu Konfirmasi</span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 text-secondary"></i>
                        Belum ada riwayat setoran sampah.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>