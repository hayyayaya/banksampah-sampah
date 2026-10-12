<?php
include 'koneksi.php';
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
        exit;
    } else if ($_SESSION['role'] == 'siswa') {
        header("Location: siswa/index.php");
        exit;
    } else if ($_SESSION['role'] == 'guru') {
        header("Location: guru/index.php");
        exit;
    } else if ($_SESSION['role'] == 'pengepul') {
        header("Location: pengepul/index.php");
        exit;
    }
}
$error = '';
if (isset($_POST['login'])) {
    $usn = mysqli_real_escape_string($koneksi, $_POST['usn']);
    $pw  = md5($_POST['pw']);
    $query  = mysqli_query($koneksi, "SELECT * FROM user WHERE usn='$usn' AND pw='$pw'");
    $cek    = mysqli_num_rows($query);
    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama']    = $data['nama'];
        $_SESSION['role']    = $data['role'];
        if ($data['role'] == 'admin') {
            header("Location: admin/index.php");
            exit;
        } else if ($data['role'] == 'siswa') {
            header("Location: siswa/index.php");
            exit;
        } else if ($data['role'] == 'guru') {
            header("Location: guru/index.php");
            exit;
        } else if ($data['role'] == 'pengepul') {
            header("Location: pengepul/index.php");
            exit;
        } else {
            $error = 'Role tidak memiliki akses sistem.';
        }
    } else {
        $error = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bank Sampah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1);
            position: relative;
            margin-top: 40px;
        }
        .avatar-container {
            position: absolute;
            top: -40px;
            left: calc(50% - 40px);
            width: 80px;
            height: 80px;
            background: #198754;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-success-subtle d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card login-card p-4 pt-5">
                    <div class="avatar-container">
                        <i class="bi bi-person-fill"></i>
                    </div>

                    <div class="card-body px-2">
                        <div class="text-center mb-4 mt-2">
                            <h3 class="fw-bold text-success m-0">Portal Login</h3>
                            <small class="text-muted">Bank Sampah</small>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 rounded-3 text-center small"><?= $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" name="usn" class="form-control rounded-3" id="usn" placeholder="Username" required autofocus>
                                <label for="usn">Username</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" name="pw" class="form-control rounded-3" id="pw" placeholder="Password" required>
                                <label for="pw">Password</label>
                            </div>
                            <button type="submit" name="login" class="btn btn-success w-100 py-2 rounded-3 fw-bold mb-3">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>