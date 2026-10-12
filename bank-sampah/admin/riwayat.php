<?php
include '../koneksi.php';
include '../nav.html';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
if (isset($_POST['aksi']) && isset($_POST['id_transaksi'])) {
    $id_transaksi = intval($_POST['id_transaksi']);
    $aksi = $_POST['aksi'];

    $q_detail = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi'");
    $trx = mysqli_fetch_assoc($q_detail);
    if ($trx && $trx['status'] == 'pending') {
        if ($aksi == 'setujui') {
            $id_user = $trx['id_user'];
            $id_sampah = $trx['id_sampah'];
            $total_rp = $trx['total_rp'];
            $berat_kg = $trx['berat_kg'];

            mysqli_query($koneksi, "UPDATE transaksi SET status = 'di setujui' WHERE id_transaksi = '$id_transaksi'");
            mysqli_query($koneksi, "UPDATE user SET saldo = saldo + $total_rp WHERE id_user = '$id_user'");
            mysqli_query($koneksi, "UPDATE sampah SET stok_kg = stok_kg + $berat_kg WHERE id_sampah = '$id_sampah'");

        } elseif ($aksi == 'tolak') {
            mysqli_query($koneksi, "UPDATE transaksi SET status = 'ditolak' WHERE id_transaksi = '$id_transaksi'");
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$query = "SELECT t.*, u.nama, s.jenis_sampah 
          FROM transaksi t 
          JOIN user u ON t.id_user = u.id_user 
          LEFT JOIN sampah s ON t.id_sampah = s.id_sampah 
          ORDER BY t.tanggal DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Setor & Transaksi - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body>
    <div class="container pb-5 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-clock-history text-success me-2"></i>Konfirmasi Setor</h4>
                <p class="text-muted small m-0">Catatan lengkap seluruh aktivitas penyetoran sampah dan transaksi sistem</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card card-custom bg-white overflow-hidden p-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-success text-dark">
                            <tr>
                                <th class="py-3 px-3">Tanggal & Waktu</th>
                                <th class="py-3">Pengguna</th>
                                <th class="py-3">Tipe</th>
                                <th class="py-3">Jenis Sampah</th>
                                <th class="py-3">Berat (Kg)</th>
                                <th class="py-3">Total Nominal</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0) : ?>
                                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td class="px-3 text-secondary small">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= date('d M Y - H:i', strtotime($row['tanggal'])); ?>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <i class="bi bi-person-circle me-1 text-muted"></i>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </td>
                                    <td>
                                        <?php if (strtolower($row['tipe']) == 'setor') : ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-arrow-down-left me-1"></i>Setor
                                            </span>
                                        <?php else : ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="bi bi-arrow-up-right me-1"></i><?= ucfirst($row['tipe']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-secondary">
                                        <?= !empty($row['jenis_sampah']) ? htmlspecialchars($row['jenis_sampah']) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="fw-medium">
                                        <?= !empty($row['berat_kg']) && $row['berat_kg'] > 0 ? $row['berat_kg'] . ' kg' : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rp <?= number_format($row['total_rp'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $status = isset($row['status']) ? $row['status'] : 'pending';
                                        if ($status == 'di setujui') : ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Disetujui</span>
                                        <?php elseif ($status == 'ditolak') : ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                        <?php else : ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (($row['status'] ?? 'pending') == 'pending') : ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Setujui transaksi ini?');">
                                                <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi']; ?>">
                                                <input type="hidden" name="aksi" value="setujui">
                                                <button type="submit" class="btn btn-success btn-sm me-1 rounded-pill px-3">
                                                    <i class="bi bi-check-lg"></i> Setuju
                                                </button>
                                            </form>

                                            <form method="POST" class="d-inline" onsubmit="return confirm('Tolak transaksi ini?');">
                                                <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi']; ?>">
                                                <input type="hidden" name="aksi" value="tolak">
                                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">
                                                    <i class="bi bi-x-lg"></i> Tolak
                                                </button>
                                            </form>
                                        <?php else : ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                        Belum ada riwayat transaksi setor.
                                    </td>
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