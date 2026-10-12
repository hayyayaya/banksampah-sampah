<?php
include '../koneksi.php';
include 'nav.html';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pengepul') {
    header("Location: ../index.php");
    exit;
}
$id_user = $_SESSION['id_user'];
$pesan = '';
$tipe_pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_beli'])) {
    $id_sampah = mysqli_real_escape_string($koneksi, $_POST['id_sampah']);
    $jumlah_kg = floatval($_POST['jumlah_kg']);

    $q_cek = mysqli_query($koneksi, "SELECT * FROM sampah WHERE id_sampah='$id_sampah'");
    $d_sampah = mysqli_fetch_assoc($q_cek);

    $q_pengepul = mysqli_query($koneksi, "SELECT saldo FROM user WHERE id_user='$id_user'");
    $d_pengepul = mysqli_fetch_assoc($q_pengepul);

    $q_admin = mysqli_query($koneksi, "SELECT id_user FROM user WHERE role='admin' LIMIT 1");
    $d_admin = mysqli_fetch_assoc($q_admin);
    if ($d_sampah && $d_admin) {
        $stok_sekarang = $d_sampah['stok_kg'];
        $harga_per_kg = $d_sampah['harga_per_kg'];
        $saldo_pengepul = $d_pengepul['saldo'];
        $id_admin = $d_admin['id_user'];
        
        $total_harga = $jumlah_kg * $harga_per_kg;

        if ($jumlah_kg <= 0) {
            $pesan = "Jumlah pembelian harus lebih dari 0 Kg.";
            $tipe_pesan = "danger";
        } elseif ($jumlah_kg > $stok_sekarang) {
            $pesan = "Stok tidak mencukupi. Stok tersedia: " . $stok_sekarang . " Kg.";
            $tipe_pesan = "danger";
        } elseif ($saldo_pengepul < $total_harga) {
            $pesan = "Saldo Anda tidak mencukupi untuk melakukan pembelian ini. Saldo Anda: Rp " . number_format($saldo_pengepul, 0, ',', '.');
            $tipe_pesan = "danger";
        } else {
            $tanggal = date('Y-m-d H:i:s');
            mysqli_begin_transaction($koneksi);

            try {
                $insert = mysqli_query($koneksi, "INSERT INTO transaksi (id_user, id_sampah, tipe, berat_kg, total_rp, tanggal, status) VALUES ('$id_user', '$id_sampah', 'jual_pengepul', '$jumlah_kg', '$total_harga', '$tanggal', 'di setujui')");

                $update_sampah = mysqli_query($koneksi, "UPDATE sampah SET stok_kg = stok_kg - $jumlah_kg WHERE id_sampah='$id_sampah'");

                $update_pengepul = mysqli_query($koneksi, "UPDATE user SET saldo = saldo - $total_harga WHERE id_user='$id_user'");

                $update_admin = mysqli_query($koneksi, "UPDATE user SET saldo = saldo + $total_harga WHERE id_user='$id_admin'");

                if ($insert && $update_sampah && $update_pengepul && $update_admin) {
                    mysqli_commit($koneksi);
                    $pesan = "Pembelian berhasil! Saldo Anda terpotong dan saldo admin bertambah.";
                    $tipe_pesan = "success";
                } else {
                    throw new Exception(mysqli_error($koneksi));
                }
            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $pesan = "Gagal memproses transaksi: " . $e->getMessage();
                $tipe_pesan = "danger";
            }
        }
    }
}
$q_sampah = mysqli_query($koneksi, "SELECT * FROM sampah WHERE stok_kg > 0");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beli Sampah - Bank Sampah</title>
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
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1 text-dark">Form Pembelian Sampah</h5>
                <p class="text-muted small mb-0">Pilih jenis sampah, masukkan jumlah pembelian, dan lakukan transaksi langsung.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
        <?php if (!empty($pesan)) : ?>
            <div class="alert alert-<?= $tipe_pesan; ?> alert-dismissible fade show" role="alert">
                <?= $pesan; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card card-custom shadow-sm p-3">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cart-plus text-success me-2"></i>Form Beli Sampah</h6>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="id_sampah" class="form-label fw-semibold">Pilih Jenis Sampah</label>
                                <select name="id_sampah" id="id_sampah" class="form-select" required onchange="hitungTotal()">
                                    <option value="" data-harga="0">-- Pilih Sampah --</option>
                                    <?php 
                                    mysqli_data_seek($q_sampah, 0);
                                    while ($s = mysqli_fetch_assoc($q_sampah)) : 
                                    ?>
                                        <option value="<?= $s['id_sampah']; ?>" data-harga="<?= $s['harga_per_kg']; ?>">
                                            <?= htmlspecialchars($s['jenis_sampah']); ?> - Rp <?= number_format($s['harga_per_kg'], 0, ',', '.'); ?>/kg (Stok: <?= $s['stok_kg']; ?> kg)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="jumlah_kg" class="form-label fw-semibold">Jumlah (Kg)</label>
                                <input type="number" step="0.01" name="jumlah_kg" id="jumlah_kg" class="form-control" placeholder="Masukkan jumlah kg" required oninput="hitungTotal()">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Harga Per Kg</label>
                                <input type="text" id="harga_tampil" class="form-control bg-light" value="Rp 0" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Total Harga</label>
                                <input type="text" id="total_tampil" class="form-control bg-light fw-bold text-success" value="Rp 0" readonly>
                            </div>

                            <button type="submit" name="proses_beli" class="btn btn-success w-100 rounded-pill fw-semibold">
                                <i class="bi bi-check-circle me-1"></i> Beli Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function hitungTotal() {
            const selectSampah = document.getElementById('id_sampah');
            const selectedOption = selectSampah.options[selectSampah.selectedIndex];
            const harga = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
            const jumlah = parseFloat(document.getElementById('jumlah_kg').value) || 0;
            
            document.getElementById('harga_tampil').value = 'Rp ' + harga.toLocaleString('id-ID');
            
            const total = harga * jumlah;
            document.getElementById('total_tampil').value = 'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>
</html>