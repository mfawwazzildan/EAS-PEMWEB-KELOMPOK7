<?php
include 'koneksi.php';

$success_message = "";
$error_message = "";

if (isset($_POST['submit'])) {

    $nama     = $_POST['nama'];
    $npm      = $_POST['npm'];
    $email    = $_POST['email'];
    $telepon  = $_POST['telepon'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $level    = 'Mahasiswa';

    $stmt_cek = mysqli_prepare($conn, "SELECT id_user FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt_cek, "s", $email);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);

    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        $error_message = "Email .";
        mysqli_stmt_close($stmt_cek);
    } else {
        mysqli_stmt_close($stmt_cek);

        $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (nama, npm, email, telp, password, level) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "ssssss", $nama, $npm, $email, $telepon, $password, $level);
        
        if (mysqli_stmt_execute($stmt_insert)) {
            $success_message = "Registrasi berhasil. Silakan login.";
        } else {
            $error_message = "Registrasi gagal.";
        }
        mysqli_stmt_close($stmt_insert);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrasi - UPNVJT Music</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --green: #0A5C2C;
            --green-dark: #084923;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-fluid,
        .row {
            min-height: 100vh;
        }

        .login-banner {
            background:
                linear-gradient(rgba(10, 92, 44, .88),
                    rgba(8, 73, 35, .95)),
                url('https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=1200') center center/cover no-repeat;

            min-height: 100vh;
        }

        .form-control-custom {
            padding: 14px 16px 14px 46px;
            border-radius: 12px;
            border: 1px solid #ced4da;
        }

        .form-control-custom:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 .25rem rgba(10, 92, 44, .15);
        }

        .input-group-custom {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #6c757d;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #6c757d;
            z-index: 10;
        }

        .btn-register {
            background: var(--green);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
        }

        .btn-register:hover {
            background: var(--green-dark);
            color: white;
        }

        .text-success-custom {
            color: var(--green);
        }

        .register-container {
            max-width: 460px;
        }
    </style>
</head>

<body>

    <div class="container-fluid p-0">

        <div class="row g-0">

            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between login-banner text-white p-5">

                <div></div>

                <div class="ps-4">

                    <i class="bi bi-music-note-list display-4 mb-4"></i>

                    <h1 class="display-4 fw-bold mb-4">
                        Melodi dalam<br>Satu Harmoni
                    </h1>

                    <p class="fs-5 text-white-50"> Sistem peminjaman instrumen musik terintegrasi
                        untuk mendukung kreativitas akademik civitas UPN Veteran Jawa Timur.
                    </p>

                    <div class="d-flex gap-5 mt-5 pt-4">

                        <div>
                            <h2 class="fw-bold">500+</h2>
                            <small>Instrumen</small>
                        </div>

                        <div>
                            <h2 class="fw-bold">24/7</h2>
                            <small>Akses Portal</small>
                        </div>

                    </div>

                </div>

                <div class="ps-4">
                    <small>
                        © 2026 UPN Veteran Jawa Timur
                    </small>
                </div>

            </div>

            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white p-4">

                <div class="register-container w-100">

                    <div class="d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-music-note-beamed fs-2 text-success-custom"></i>
                        <h3 class="fw-bold text-success-custom m-0">
                            UPNVJT Music
                        </h3>
                    </div>

                    <h1 class="fw-bold mb-2">
                        Buat Akun Baru
                    </h1>

                    <p class="text-muted mb-4">
                        Daftarkan akun Anda untuk menggunakan sistem inventaris musik.
                    </p>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <?= $success_message ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <?= $error_message ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Lengkap
                            </label>

                            <div class="input-group-custom">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" name="nama" class="form-control form-control-custom" placeholder="Contoh: Kiel Alfarez Limbong" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                NPM
                            </label>

                            <div class="input-group-custom">
                                <i class="bi bi-card-text input-icon"></i>
                                <input type="text" name="npm" class="form-control form-control-custom" placeholder="Contoh: 24081010050" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Email
                            </label>

                            <div class="input-group-custom">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" name="email" class="form-control form-control-custom" placeholder="Contoh: mahasiswa@upnvjt.ac.id" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nomor Telepon
                            </label>

                            <div class="input-group-custom">
                                <i class="bi bi-telephone input-icon"></i>
                                <input type="text" name="telepon" class="form-control form-control-custom" placeholder="Contoh: 081234567890" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Kata Sandi
                            </label>

                            <div class="input-group-custom">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" name="password" id="password" class="form-control form-control-custom" placeholder="••••••" required>
                                <button
                                    type="button" class="toggle-password" id="btn-toggle-pass">
                                    <i class="bi bi-eye" id="icon-toggle-pass"></i>
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit" name="submit" class="btn btn-register w-100">
                            Buat Akun
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        Sudah punya akun?
                        <a href="login.php"
                            class="text-success-custom fw-bold text-decoration-none">
                            Masuk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('btn-toggle-pass')
            .addEventListener('click', function() {

                let password = document.getElementById('password');
                let icon = document.getElementById('icon-toggle-pass');

                if (password.type === 'password') {
                    password.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    password.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }

            });
    </script>

</body>

</html>