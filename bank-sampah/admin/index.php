<?php
include '../koneksi.php';
include '../nav.html';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$id_admin  = $_SESSION['id_user'];

$q_admin   = mysqli_query($koneksi, "SELECT saldo FROM user WHERE id_user = '$id_admin'");
$d_admin   = mysqli_fetch_assoc($q_admin);
$saldo_admin = $d_admin['saldo'] ?? 0;

$q_user    = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM user");
$d_user    = mysqli_fetch_assoc($q_user);

$q_sampah  = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM sampah");
$d_sampah  = mysqli_fetch_assoc($q_sampah);

$q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tarik_saldo WHERE status='pending'");
$d_pending = mysqli_fetch_assoc($q_pending);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #198754 0%, #0d462b 100%);
        }
        .stat-card {
            border: none;
            border-radius: 16px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
        .icon-shape {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .menu-btn {
            border: none;
            border-radius: 12px;
            padding: 1.2rem 1rem;
            font-weight: 600;
            transition: all 0.2s ease;
            background: #ffffff;
            color: #2b3035;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .menu-btn:hover {
            background: #198754;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(25, 135, 84, 0.2);
        }
    </style>
</head>
<body>
    <div class="container pb-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">Selamat Datang, <span class="text-success"><?= htmlspecialchars($_SESSION['nama']); ?></span>!</h4>
                <p class="text-muted small m-0">Ringkasan statistik dan menu pengelolaan sistem</p>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-success bg-gradient text-white shadow-sm p-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-white-50 fw-medium small">Total Saldo</p>
                            <h3 class="fw-bold m-0">Rp <?= number_format($saldo_admin, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="icon-shape">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-primary bg-gradient text-white shadow-sm p-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-white-50 fw-medium small">Total Pengguna</p>
                            <h2 class="fw-bold m-0"><?= $d_user['total']; ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info bg-gradient text-white shadow-sm p-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-white-50 fw-medium small">Kategori Sampah</p>
                            <h2 class="fw-bold m-0"><?= $d_sampah['total']; ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="bi bi-trash"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-danger bg-gradient text-white shadow-sm p-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-white-50 fw-medium small">Pending Penarikan</p>
                            <h2 class="fw-bold m-0"><?= $d_pending['total']; ?></h2>
                        </div>
                        <div class="icon-shape">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-3 text-secondary">Menu Utama</h5>
        
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <a href="saldo.php" class="btn menu-btn w-100">
                    <i class="bi bi-cash-stack fs-5 text-success"></i> Tambah Saldo 
                </a>
            </div>
            <div class="col-md-4">
                <a href="sampah.php" class="btn menu-btn w-100">
                    <i class="bi bi-trash-fill fs-5"></i> Kelola Sampah
                </a>
            </div>
            <div class="col-md-4">
                <a href="user.php" class="btn menu-btn w-100">
                    <i class="bi bi-person-gear fs-5"></i> Kelola Akun User
                </a>
            </div>
        </div>

        <div class="row g-3 justify-content-center">
            <div class="col-md-4">
                <a href="riwayat_beli.php" class="btn menu-btn w-100">
                    <i class="bi bi-cart-check-fill fs-5"></i> Riwayat Pengepul Beli
                </a>
            </div>
            <div class="col-md-4">
                <a href="riwayat.php" class="btn menu-btn w-100">
                    <i class="bi bi-receipt fs-5"></i> Konfirmasi Setor
                </a>
            </div>
            <div class="col-md-4">
                <a href="konfirmasi.php" class="btn menu-btn w-100">
                    <i class="bi bi-wallet2 fs-5"></i> Konfirmasi Tarik Saldo
                </a>
            </div>
        </div>
    </div>
</body>
</html>