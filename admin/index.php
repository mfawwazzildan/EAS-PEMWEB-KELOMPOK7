<?php
include '../koneksi.php';
session_start();

if (!isset($_SESSION['login_user']) || !isset($_SESSION['level_user'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['level_user'] === 'Mahasiswa') {
    header("Location: ../index.php");
    exit;
}

if ($_SESSION['level_user'] !== 'Admin') {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$q_users = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE level = 'Mahasiswa'"); // Sesuaikan kondisi jika ingin menghitung semua user
$res_users = mysqli_fetch_assoc($q_users);
$total_users = $res_users['total'] ?? 0;

$q_peminjaman = mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman");
$res_peminjaman = mysqli_fetch_assoc($q_peminjaman);
$total_peminjaman = $res_peminjaman['total'] ?? 0;

$q_kategori = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kategori");
$res_kategori = mysqli_fetch_assoc($q_kategori);
$total_kategori = $res_kategori['total'] ?? 0;

$q_barang = mysqli_query($conn, "SELECT COUNT(*) AS total FROM barang");
$res_barang = mysqli_fetch_assoc($q_barang);
$total_barang = $res_barang['total'] ?? 0;


$q_denda = mysqli_query($conn, "SELECT SUM(jumlah_denda) AS total FROM denda WHERE status_denda = 'Belum Lunas'");
$res_denda = mysqli_fetch_assoc($q_denda);
$total_denda = $res_denda['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UPNVJT Music</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="admin.css">

    <style>
        .stats-grid-5 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-card i {
            font-size: 28px;
            opacity: 0.8;
        }
    </style>
</head>

<body>

    <div class="admin-layout">

        <?php include 'assets/navbar.php' ?>

        <main class="content-admin">

            <div class="topbar-admin">
                <div>
                    <h1>Dashboard</h1>
                    <p>Welcome!</p>
                </div>
            </div>

            <div class="stats-grid-5">

                <div class="stats-card">
                    <div>
                        <span>Total Users</span>
                        <h2><?= $total_users; ?></h2>
                        <small>Registered students</small>
                    </div>
                    <i class="bi bi-people-fill"></i>
                </div>

                <div class="stats-card">
                    <div>
                        <span>Peminjaman</span>
                        <h2><?= $total_peminjaman; ?></h2>
                        <small>Total loan records</small>
                    </div>
                    <i class="bi bi-arrow-left-right"></i>
                </div>

                <div class="stats-card">
                    <div>
                        <span>Kategori</span>
                        <h2><?= $total_kategori; ?></h2>
                        <small>Instrument types</small>
                    </div>
                    <i class="bi bi-tags-fill" style="font-size: 28px;"></i>
                </div>

                <div class="stats-card">
                    <div>
                        <span style="font-size: 14px;">Total Barang</span>
                        <h2 style="font-size: 32px; font-weight: 700; margin: 4px 0;"><?= $total_barang; ?></h2>
                        <small>Unique items</small>
                    </div>
                    <i class="bi bi-music-note-list" style="font-size: 28px;"></i>
                </div>

                <div class="stats-card">
                    <div>
                        <span>Total Denda</span>
                        <h2 style="font-size: 20px; font-weight: 700; margin: 8px 0;">Rp <?= number_format($total_denda, 0, ',', '.'); ?></h2>
                        <small>Unpaid penalties</small>
                    </div>
                    <i class="bi bi-cash-coin" style="font-size: 28px;"></i>
                </div>

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>    
</body>

</html>