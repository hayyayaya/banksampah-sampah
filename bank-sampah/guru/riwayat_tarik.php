<?php
include '../koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') {
    header("Location: ../index.php");
    exit;
}
$id_user = $_SESSION['id_user'];
$q_user  = mysqli_query($koneksi, "SELECT saldo FROM user WHERE id_user='$id_user'");
$user    = mysqli_fetch_assoc($q_user);
$saldo_bisa_ditarik = $user['saldo'];

if (isset($_POST['tarik_semua'])) {
    $tanggal = date('Y-m-d H:i:s');
    if ($saldo_bisa_ditarik <= 0) {
        echo "<script>alert('Tidak ada saldo yang dapat dicairkan!');</script>";
    } else {
        mysqli_begin_transaction($koneksi);
        try {
            mysqli_query($koneksi, "UPDATE user SET saldo = saldo - $saldo_bisa_ditarik WHERE id_user = '$id_user'");
            mysqli_query($koneksi, "INSERT INTO tarik_saldo (id_user, nominal, status, tanggal) 
                                    VALUES ('$id_user', '$saldo_bisa_ditarik', 'pending', '$tanggal')");
            mysqli_commit($koneksi);
            echo "<script>alert('Pengajuan penarikan sebesar Rp " . number_format($saldo_bisa_ditarik, 0, ',', '.') . " berhasil dikirim! Saldo Anda telah dipotong dan menunggu persetujuan admin.'); location.href='riwayat_tarik.php';</script>";
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            echo "<script>alert('Gagal mengajukan penarikan. Silakan coba lagi.');</script>";
        }
    }
}

$query  = "SELECT * FROM tarik_saldo WHERE id_user = '$id_user' ORDER BY tanggal DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pencairan Saldo</title>
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
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-success py-3 shadow-sm">
        <div class="container-fluid px-3 px-md-5">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2 m-0" href="index.php">
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
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-white-50 small">Total Saldo Aktif</span>
                    <i class="bi bi-wallet2 fs-4 text-white-50"></i>
                </div>
                <h2 class="fw-bold mb-3">Rp <?= number_format($user['saldo'], 0, ',', '.'); ?></h2>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-white-10 small">
                    <span class="text-white-50">Saldo dapat dicairkan</span>
                    <span class="fw-bold">Rp <?= number_format($saldo_bisa_ditarik, 0, ',', '.'); ?></span>
                </div>
            </div>

            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mencairkan seluruh saldo sebesar Rp <?= number_format($saldo_bisa_ditarik, 0, ',', '.'); ?>?')">
                <button type="submit" name="tarik_semua" class="btn btn-outline-primary w-100 fw-bold py-2 rounded-3" <?= ($saldo_bisa_ditarik <= 0) ? 'disabled' : ''; ?>>
                    Cairkan Saldo (Rp <?= number_format($saldo_bisa_ditarik, 0, ',', '.'); ?>)
                </button>
            </form>
        </div>

        <h5 class="fw-bold text-dark mb-3">Riwayat Pencairan</h5>

        <div class="card card-custom shadow-sm p-2">
            <div class="card-body p-0">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($row['status'] == 'ditolak'): ?>
                                        <div class="bg-danger-subtle text-danger p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="bi bi-arrow-down-left-circle fs-4"></i>
                                        </div>
                                    <?php elseif ($row['status'] == 'selesai' || $row['status'] == 'berhasil'): ?>
                                        <div class="bg-success-subtle text-success p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="bi bi-check-circle fs-4"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-warning-subtle text-warning p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="bi bi-arrow-up-right-circle fs-4"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">
                                            <?= ($row['status'] == 'ditolak') ? 'Pengembalian Saldo (Penarikan Ditolak)' : 'Penarikan Saldo'; ?>
                                        </h6>
                                        <div class="text-muted small">
                                            <?= date('d M Y, H:i', strtotime($row['tanggal'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?php if ($row['status'] == 'ditolak'): ?>
                                        <span class="fw-bold text-success fs-6">+Rp <?= number_format($row['nominal'], 0, ',', '.'); ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold text-danger fs-6">-Rp <?= number_format($row['nominal'], 0, ',', '.'); ?></span>
                                    <?php endif; ?>

                                    <div>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($row['status'] == 'selesai' || $row['status'] == 'berhasil'): ?>
                                            <span class="badge bg-success">Selesai</span>
                                        <?php elseif ($row['status'] == 'ditolak'): ?>
                                            <span class="badge bg-danger">Ditolak (Saldo Dikembalikan)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 text-secondary"></i>
                        Belum ada riwayat penarikan saldo.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>