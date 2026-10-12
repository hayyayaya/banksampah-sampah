<?php
include '../koneksi.php';
include '../nav.html';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
if (isset($_POST['tambah_saldo'])) {
    $id_user_tujuan =$_POST['id_user'];
    $jumlah =$_POST['jumlah'];
    if (!empty($id_user_tujuan) &&$jumlah > 0) {
        mysqli_query($koneksi, "UPDATE user SET saldo = saldo + $jumlah WHERE id_user = '$id_user_tujuan'");
        echo "<script>alert('Saldo berhasil ditambahkan ke user!'); location.href='saldo.php';</script>";
    } else {
        echo "<script>alert('Pilih user dan masukkan nominal yang valid (> 0)!'); location.href='saldo.php';</script>";
    }
}
$q_users = mysqli_query($koneksi, "SELECT * FROM user WHERE role IN ('admin', 'pengepul') ORDER BY nama ASC");
$users_data = [];
while ($u = mysqli_fetch_assoc($q_users)) {
    $users_data[] =$u;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldo Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-wallet2 text-success me-2"></i>Tambah Saldo User</h4>
                <p class="text-muted small m-0">Kelola dan isi saldo user berdasarkan role langsung dari halaman ini</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card card-custom bg-white p-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4 text-success d-flex align-items-center gap-2">
                            <i class="bi bi-plus-circle-fill"></i> Form Isi Saldo User
                        </h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">Pilih Role</label>
                                <select id="pilih_role" class="form-select form-select-lg" required onchange="filterUserByRole()">
                                    <option value="" disabled selected>-- Pilih Role --</option>
                                    <option value="admin">Admin</option>
                                    <option value="pengepul">Pengepul</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">Pilih Nama User</label>
                                <select name="id_user" id="pilih_user" class="form-select form-select-lg" required disabled>
                                    <option value="" disabled selected>-- Pilih Role Terlebih Dahulu --</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-secondary small fw-bold">Nominal Isi Saldo (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary fw-bold">Rp</span>
                                    <input type="number" name="jumlah" class="form-control form-control-lg" placeholder="Masukkan nominal (contoh: 50000)" min="1" required>
                                </div>
                            </div>

                            <button type="submit" name="tambah_saldo" class="btn btn-success w-100 py-2 fw-bold shadow-sm rounded-3">
                                <i class="bi bi-check-circle me-1"></i> Tambah Saldo Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const allUsers = <?= json_encode($users_data); ?>;

        function filterUserByRole() {
            const selectedRole = document.getElementById('pilih_role').value;
            const userSelect = document.getElementById('pilih_user');

            userSelect.innerHTML = '<option value="" disabled selected>-- Pilih Nama User --</option>';
            userSelect.disabled = false;

            const filteredUsers = allUsers.filter(user => user.role === selectedRole);
            if (filteredUsers.length === 0) {
                userSelect.innerHTML = '<option value="" disabled selected>-- Tidak ada user pada role ini --</option>';
                userSelect.disabled = true;
                return;
            }
            filteredUsers.forEach(user => {
                const formattedSaldo = new Intl.NumberFormat('id-ID').format(user.saldo);
                const option = document.createElement('option');
                option.value = user.id_user;
                option.textContent = `${user.nama} - Saldo Saat Ini: Rp ${formattedSaldo}`;
                userSelect.appendChild(option);
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>