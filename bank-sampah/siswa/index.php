<?php
include '../koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$q_user  = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user='$id_user'");
$user    = mysqli_fetch_assoc($q_user);

$q_setor = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE id_user='$id_user' AND tipe='setor'");
$total_setor = mysqli_fetch_assoc($q_setor)['total'];

$q_tarik_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total, COALESCE(SUM(nominal),0) as total_rp FROM tarik_saldo WHERE id_user='$id_user' AND status='pending'");
$d_pending = mysqli_fetch_assoc($q_tarik_pending);

$q_tarik_sukses = mysqli_query($koneksi, "SELECT COUNT(*) as total, COALESCE(SUM(nominal),0) as total_rp FROM tarik_saldo WHERE id_user='$id_user' AND status='berhasil'");
$d_sukses = mysqli_fetch_assoc($q_tarik_sukses);

$q_tarik_ditolak = mysqli_query($koneksi, "SELECT COUNT(*) as total, COALESCE(SUM(nominal),0) as total_rp FROM tarik_saldo WHERE id_user='$id_user' AND status='ditolak'");
$d_ditolak = mysqli_fetch_assoc($q_tarik_ditolak);

$total_pending = $d_pending['total'];

$labels_hari = [];
$data_nominal = [];

$nama_hari = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];

for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $hari_eng = date('l', strtotime($tgl));
    $hari_indo = $nama_hari[$hari_eng];
    $tgl_formatted = date('d/m', strtotime($tgl));
    
    if ($i == 0) {
        $labels_hari[] = "Hari Ini";
    } elseif ($i == 1) {
        $labels_hari[] = "Kemarin";
    } else {
        $labels_hari[] = "$hari_indo ($tgl_formatted)";
    }

    $q_harian = mysqli_query($koneksi, "SELECT COALESCE(SUM(nominal),0) as total FROM tarik_saldo WHERE id_user='$id_user' AND DATE(tanggal) = '$tgl' AND status!='ditolak'");
    $d_harian = mysqli_fetch_assoc($q_harian);
    $data_nominal[] = (int)$d_harian['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard siswa - Bank Sampah</title>
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
        @media (min-width: 768px) {
            .border-end-md {
                border-right: 1px solid #dee2e6 !important;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-success py-3 shadow-sm">
        <div class="container-fluid px-3 px-md-5">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2 m-0" href="index.php">
                <i class="bi bi-recycle fs-4"></i> Bank Sampah
            </a>
            <div class="d-flex gap-2 align-items-center ms-auto">
                <span class="text-white me-2 d-none d-md-inline small">Halo, <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong></span>
                <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')" class="btn btn-outline-light btn-sm px-3 rounded-pill">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-3 px-md-5 my-4">
        
        <div class="mb-4">
            <h5 class="fw-bold mb-1 text-dark">Layanan & Aktivitas siswa</h5>
            <p class="text-muted small mb-3">Akses cepat ke transaksi penyetoran sampah dan riwayat Anda.</p>
            
            <div class="row g-3">
                <div class="col-md-6 col-6">
                    <a href="setor.php" class="text-decoration-none">
                        <div class="card card-custom shadow-sm h-100 p-2">
                            <div class="card-body">
                                <p class="text-muted small mb-2">Setor Sampah</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <i class="bi bi-box-arrow-in-down text-success icon-box"></i>
                                    <span class="fw-bold fs-4 text-dark">+ Input</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6 col-6">
                    <a href="riwayat_setor.php" class="text-decoration-none">
                        <div class="card card-custom shadow-sm h-100 p-2">
                            <div class="card-body">
                                <p class="text-muted small mb-2">Riwayat Setoran</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <i class="bi bi-receipt text-primary icon-box"></i>
                                    <span class="fw-bold fs-4 text-dark"><?= $total_setor; ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <h5 class="fw-bold mb-3 text-dark">Keuangan Akun</h5>
            <div class="card card-custom shadow-sm p-3">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-5 border-end-md">
                            <p class="text-muted small mb-1">Status Penarikan Pendapatan</p>
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-arrow-repeat text-warning fs-2"></i>
                                <div>
                                    <span class="fw-bold fs-4 text-dark"><?= $total_pending; ?> Pengajuan</span>
                                    <div class="text-muted small">Menunggu Konfirmasi Admin</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 d-flex justify-content-between align-items-center ps-md-4">
                            <div>
                                <p class="text-muted small mb-1">Saldo Tersedia</p>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-cash-stack text-success fs-2"></i>
                                    <h2 class="text-success fw-bold mb-0">Rp <?= number_format($user['saldo'], 0, ',', '.'); ?></h2>
                                </div>
                            </div>
                            <div>
                                <a href="riwayat_tarik.php" class="btn btn-link text-decoration-none fw-bold text-success pe-0">
                                    Lihat Detail <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="card card-custom shadow-sm p-3 bg-white">
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-bar-chart-line-fill text-success me-2"></i>Grafik Penarikan Saldo (7 Hari Terakhir)
                    </h5>
                    <div style="width: 100%; height: 300px; position: relative;">
                        <canvas id="chart7Hari"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('chart7Hari').getContext('2d');
        const chart7Hari = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels_hari); ?>,
                datasets: [{
                    label: 'Total Penarikan (Rp)',
                    data: <?= json_encode($data_nominal); ?>,
                    backgroundColor: '#198754',
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Nominal: Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>