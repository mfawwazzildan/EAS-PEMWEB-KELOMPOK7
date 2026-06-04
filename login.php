<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login_user']) && isset($_SESSION['level_user'])) {
    if ($_SESSION['level_user'] === 'Admin') {
        header("Location: admin/index.php");
        exit;
    } else if ($_SESSION['level_user'] === 'Mahasiswa') {
        header("Location: index.php");
        exit;
    }
}

$error_message = "";

if (isset($_POST['submit'])) {
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; 

    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $query = mysqli_query($conn, $sql);

    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);

        if (password_verify($password, $row['password'])) {
   
            $_SESSION['id_user']    = $row['id_user']; 
            $_SESSION['nama_user']  = $row['nama'];   
            $_SESSION['login_user'] = true;            
            $_SESSION['level_user'] = $row['level'];   
 
            if ($row['level'] === 'Admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
            
        } else {
            $error_message = "Kata sandi yang Anda masukkan salah.";
        }
    } else {
        $error_message = "Email tidak terdaftar.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - UPNVJT Music</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --text-success-custom: #0A5C2C;
            --bg-success-custom: #0A5C2C;
            --bg-content: #FAFCFF;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-content);
            color: #333;
            min-height: 100vh;
        }

        .login-banner {
            background: linear-gradient(rgba(10, 92, 44, 0.88), rgba(8, 73, 35, 0.95)), 
                        url('https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=1000') no-repeat center center;
            background-size: cover;
            min-height: 100vh;
        }

        .form-control-custom {
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border-radius: 10px;
            border: 1px solid #CED4DA;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            border-color: var(--text-success-custom);
            box-shadow: 0 0 0 0.25rem rgba(10, 92, 44, 0.15);
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6C757D;
            z-index: 10;
            font-size: 1.1rem;
        }

        .input-group-custom .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6C757D;
            z-index: 10;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .btn-login-custom {
            background-color: var(--bg-success-custom);
            color: #fff;
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-login-custom:hover {
            background-color: #084923;
            color: #fff;
        }

        .text-success-custom {
            color: var(--text-success-custom) !important;
            text-decoration: none;
        }
        .text-success-custom:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container-fluid p-0">
        <div class="row g-0 align-items-center">
            
            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between login-banner p-5 text-white">
                <div></div>
                
                <div class="max-w-md ps-4">
                    <div class="mb-4">
                        <i class="bi bi-music-note-list display-4"></i>
                    </div>
                    <h1 class="display-5 fw-bold mb-3" style="line-height: 1.2;">Melodi dalam<br>Satu Harmoni</h1>
                    <p class="fs-5 text-white-50 fw-light mb-5" style="max-width: 480px;">
                        Sistem peminjaman instrumen musik terintegrasi untuk mendukung kreativitas akademik civitas UPNVJT.
                    </p>
                    
                    <div class="d-flex gap-5 pt-4 border-top border-secondary border-opacity-25">
                        <div>
                            <h3 class="fw-bold m-0">500+</h3>
                            <small class="text-white-50">Instrumen</small>
                        </div>
                        <div>
                            <h3 class="fw-bold m-0">24/7</h3>
                            <small class="text-white-50">Akses Portal</small>
                        </div>
                    </div>
                </div>

                <div class="ps-4">
                    <small class="text-white-50">&copy; 2026 UPN Veteran Jawa Timur. Departemen Seni & Budaya.</small>
                </div>
            </div>

            <div class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-0 p-4 p-sm-5 d-flex flex-column justify-content-center min-vh-100 bg-white">
                <div class="mx-auto w-100" style="max-width: 420px;">
                    
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-music-note-beamed fs-3 text-success-custom"></i>
                        <h3 class="fw-bold m-0 text-success-custom">UPNVJT Music</h3>
                    </div>

                    <div class="mb-5">
                        <h2 class="fw-bold text-dark mb-2">Selamat Datang di Portal Inventaris</h2>
                        <p class="text-muted small">Silakan masuk menggunakan kredensial Akun Anda.</p>
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                            <div><?= $error_message; ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold text-dark small">Email</label>
                            <div class="input-group-custom">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" id="email" name="email" class="form-control form-control-custom" placeholder="Contoh: mahasiswa@upnvjt.ac.id" required autocomplete="username" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold text-dark small">Kata Sandi</label>
                            <div class="input-group-custom">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" id="password" name="password" class="form-control form-control-custom" placeholder="••••••••" required autocomplete="current-password">
                                <button type="button" class="toggle-password" id="btn-toggle-pass" aria-label="Tampilkan kata sandi">
                                    <i class="bi bi-eye" id="icon-toggle-pass"></i>
                                </button>
                            </div>
                        </div>


                        <button type="submit" name="submit" class="btn btn-login-custom w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm mb-5">
                            Masuk
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        Belum punya Akun?
                        <a href="registrasi.php"
                            class="text-success-custom fw-bold text-decoration-none">
                            Daftar
                        </a>
                    </div>

                    <div class="d-block d-lg-none text-center pt-4 border-top">
                        <small class="text-muted">&copy; 2026 UPN Veteran Jawa Timur.<br>Departemen Seni & Budaya.</small>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('btn-toggle-pass').addEventListener('click', function() {
            const inputPass = document.getElementById('password');
            const iconPass = document.getElementById('icon-toggle-pass');
            
            if (inputPass.type === 'password') {
                inputPass.type = 'text';
                iconPass.classList.remove('bi-eye');
                iconPass.classList.add('bi-eye-slash');
            } else {
                inputPass.type = 'password';
                iconPass.classList.remove('bi-eye-slash');
                iconPass.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>