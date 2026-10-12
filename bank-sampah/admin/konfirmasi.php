<?php
include '../koneksi.php';
include '../nav.html';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
if (isset($_GET['aksi']) && $_GET['aksi'] == 'konfirmasi') {
    $id_tarik = intval($_GET['id_tarik']);
    $id_admin = $_SESSION['id_user'];

    $q_tarik = mysqli_query($koneksi, "SELECT * FROM tarik_saldo WHERE id_tarik = '$id_tarik'");
    $d_tarik = mysqli_fetch_assoc($q_tarik);
    if ($d_tarik && $d_tarik['status'] == 'pending') {
        $nominal = $d_tarik['nominal'];
        $q_admin = mysqli_query($koneksi, "SELECT saldo FROM user WHERE id_user = '$id_admin'");
        $d_admin = mysqli_fetch_assoc($q_admin);
        $saldo_admin = $d_admin['saldo'] ?? 0;
        if ($saldo_admin < $nominal) {
            $_SESSION['pesan_error'] = "Gagal memproses penarikan! Saldo Admin tidak mencukupi (Saldo Anda: Rp " . number_format($saldo_admin, 0, ',', '.') . ").";
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
            exit;
        }
        mysqli_begin_transaction($koneksi);
        try {
            mysqli_query($koneksi, "UPDATE user SET saldo = saldo - $nominal WHERE id_user = '$id_admin'");
            mysqli_query($koneksi, "UPDATE tarik_saldo SET status = 'selesai' WHERE id_tarik = '$id_tarik'");
            $id_user = $d_tarik['id_user'];
            $keterangan = "Penarikan saldo sebesar Rp " . number_format($nominal, 0, ',', '.') . " berhasil disetujui.";
            mysqli_query($koneksi, "INSERT INTO riwayat_transaksi (id_user, tipe, nominal, keterangan, tanggal) 
                                    VALUES ('$id_user', 'penarikan', '$nominal', '$keterangan', NOW())");

            mysqli_commit($koneksi);
            $_SESSION['pesan'] = "Penarikan saldo berhasil disetujui dan saldo Admin telah dipotong.";
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $_SESSION['pesan_error'] = "Gagal memproses persetujuan penarikan.";
        }
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    }
}
if (isset($_GET['aksi']) && $_GET['aksi'] == 'tolak') {
    $id_tarik = intval($_GET['id_tarik']);
    
    $q_tarik = mysqli_query($koneksi, "SELECT * FROM tarik_saldo WHERE id_tarik = '$id_tarik'");
    $d_tarik = mysqli_fetch_assoc($q_tarik);
    if ($d_tarik && $d_tarik['status'] == 'pending') {
        $id_user = $d_tarik['id_user'];
        $nominal = $d_tarik['nominal'];
        mysqli_begin_transaction($koneksi);
        try {
            mysqli_query($koneksi, "UPDATE user SET saldo = saldo + $nominal WHERE id_user = '$id_user'");
            mysqli_query($koneksi, "UPDATE tarik_saldo SET status = 'ditolak' WHERE id_tarik = '$id_tarik'");

            $keterangan = "Pengembalian saldo (Penarikan ditolak) sebesar Rp " . number_format($nominal, 0, ',', '.');
            
            mysqli_query($koneksi, "INSERT INTO riwayat_transaksi (id_user, tipe, nominal, keterangan, tanggal) 
                                    VALUES ('$id_user', 'pengembalian', '$nominal', '$keterangan', NOW())");
            mysqli_commit($koneksi);
            $_SESSION['pesan'] = "Penarikan saldo ditolak dan saldo telah dikembalikan ke pengguna.";
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $_SESSION['pesan_error'] = "Gagal memproses penolakan penarikan.";
        }
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    }
}
$query = "SELECT ts.*, u.nama 
          FROM tarik_saldo ts 
          JOIN user u ON ts.id_user = u.id_user 
          ORDER BY ts.tanggal DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Tarik Saldo - Admin Panel</title>
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
                <h4 class="fw-bold mb-1"><i class="bi bi-cash-stack text-success me-2"></i>Konfirmasi Penarikan Saldo</h4>
                <p class="text-muted small m-0">Kelola dan persetujuan pengajuan tarik saldo dari pengguna</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i> <?= $_SESSION['pesan']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['pesan']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['pesan_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> <?= $_SESSION['pesan_error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['pesan_error']); ?>
        <?php endif; ?>

        <div class="card card-custom bg-white overflow-hidden p-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-success text-dark">
                            <tr>
                                <th class="py-3 px-3">Nama Pengguna</th>
                                <th class="py-3">Nominal Penarikan</th>
                                <th class="py-3">Tanggal Pengajuan</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="py-3 text-center">Aksi / Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0) : ?>
                                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td class="px-3 fw-semibold text-dark">
                                        <i class="bi bi-person-circle me-1 text-muted"></i>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rp <?= number_format($row['nominal'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-secondary small">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= date('d M Y - H:i', strtotime($row['tanggal'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $status = isset($row['status']) ? $row['status'] : 'pending';
                                        if ($status == 'selesai' || $status == 'berhasil') : ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                                        <?php elseif ($status == 'ditolak') : ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                        <?php else : ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (($row['status'] ?? 'pending') == 'pending') : ?>
                                            <a href="?aksi=konfirmasi&id_tarik=<?= $row['id_tarik']; ?>" onclick="return confirm('Setujui penarikan saldo ini? Saldo Anda sebagai admin akan dipotong.')" class="btn btn-success btn-sm rounded-pill px-3 me-1">
                                                <i class="bi bi-check-lg me-1"></i> Setujui
                                            </a>
                                            <a href="?aksi=tolak&id_tarik=<?= $row['id_tarik']; ?>" onclick="return confirm('Yakin ingin menolak penarikan saldo ini? Saldo akan dikembalikan ke pengguna.')" class="btn btn-danger btn-sm rounded-pill px-3">
                                                <i class="bi bi-x-lg me-1"></i> Tolak
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                        Belum ada pengajuan penarikan saldo.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>