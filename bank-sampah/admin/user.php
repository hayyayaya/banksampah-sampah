<?php
include '../koneksi.php';
include '../nav.html';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
if (isset($_POST['tambah_user'])) {
    $nama = $_POST['nama'];
    $usn  = $_POST['usn'];
    $pw   = md5($_POST['pw']);
    $role = $_POST['role'];

    mysqli_query($koneksi, "INSERT INTO user (nama, usn, pw, role, saldo) 
                            VALUES ('$nama', '$usn', '$pw', '$role', 0)");
    echo "<script>alert('User berhasil ditambahkan!'); location.href='user.php';</script>";
}
if (isset($_GET['hapus'])) {
    $id_user = $_GET['hapus'];
    if ($id_user == $_SESSION['id_user']) {
        echo "<script>alert('Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif!'); location.href='user.php';</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM user WHERE id_user = '$id_user'");
        echo "<script>alert('User berhasil dihapus!'); location.href='user.php';</script>";
    }
}
$user_list = mysqli_query($koneksi, "SELECT * FROM user ORDER BY id_user DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin Panel</title>
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
    </style>
</head>
<body>
    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-person-gear text-success me-2"></i>Kelola Akun User</h4>
                <p class="text-muted small m-0">Tambah pengguna baru dan kelola hak akses sistem</p>
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
                            <i class="bi bi-person-plus-fill"></i> Tambah User Baru
                        </h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control bg-light" placeholder="Nama lengkap" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">Username</label>
                                <input type="text" name="usn" class="form-control bg-light" placeholder="Username" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">Password</label>
                                <input type="password" name="pw" class="form-control bg-light" placeholder="Password" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-secondary small fw-bold">Role Access</label>
                                <select name="role" class="form-select bg-light">
                                    <option value="siswa">Siswa</option>
                                    <option value="guru">Guru</option>
                                    <option value="pengepul">Pengepul</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <button type="submit" name="tambah_user" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                <i class="bi bi-save me-1"></i> Tambah User
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
                                        <th class="py-3">Nama</th>
                                        <th class="py-3">Username</th>
                                        <th class="py-3">Role</th>
                                        <th class="py-3">Saldo</th>
                                        <th class="py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($u = mysqli_fetch_assoc($user_list)) : ?>
                                    <tr>
                                        <td class="px-3 text-muted fw-bold">#<?= $u['id_user']; ?></td>
                                        <td class="fw-semibold text-dark"><?= $u['nama']; ?></td>
                                        <td class="text-secondary"><?= $u['usn']; ?></td>
                                        <td>
                                            <?php 
                                                $badge_color = 'bg-secondary';
                                                if ($u['role'] == 'admin') $badge_color = 'bg-danger';
                                                else if ($u['role'] == 'siswa') $badge_color = 'bg-primary';
                                                else if ($u['role'] == 'guru') $badge_color = 'bg-info text-dark';
                                                else if ($u['role'] == 'pengepul') $badge_color = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?= $badge_color; ?> px-2 py-1"><?= ucfirst($u['role']); ?></span>
                                        </td>
                                        <td class="fw-medium text-success">Rp <?= number_format($u['saldo']); ?></td>
                                        <td class="text-center">
                                            <?php if ($u['id_user'] != $_SESSION['id_user']) : ?>
                                                <a href="user.php?hapus=<?= $u['id_user']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            <?php else : ?>
                                                <span class="badge bg-light text-muted border px-3 py-2"><i class="bi bi-check-circle-fill text-success me-1"></i> Aktif</span>
                                            <?php endif; ?>
                                        </td>
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