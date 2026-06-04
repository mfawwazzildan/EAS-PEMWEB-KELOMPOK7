<?php
session_start();

if (!isset($_SESSION['login_user']) || !isset($_SESSION['level_user'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['level_user'] === 'Admin') {
    header("Location: admin/index.php");
    exit;
}

if ($_SESSION['level_user'] !== 'Mahasiswa') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPNVJT Music - Peraturan Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid pt-5 mt-4">
        <div class="row">
            <?php include 'assets/navbar.php' ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4 bg-content min-vh-100">

                <div class="mb-4">
                    <h1 class="h2 fw-bold text-success-custom mb-1">Peraturan & Ketentuan Peminjaman</h1>
                    <p class="text-muted m-0">Harap membaca dan memahami seluruh ketentuan sebelum melakukan *booking* instrumen musik.</p>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                            <h4 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-text text-success-custom"></i> Aturan Umum Peminjaman
                            </h4>

                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="bg-light p-2 rounded-3 text-success-custom fw-bold">01</div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Khusus Mahasiswa Aktif</h6>
                                        <p class="text-muted small m-0">Peminjaman hanya diperuntukkan bagi mahasiswa aktif UPN Veteran Jawa Timur yang terdaftar di sistem.</p>
                                    </div>
                                </div>
                                <hr class="my-2 text-muted opacity-25">

                                <div class="d-flex gap-3 align-items-start">
                                    <div class="bg-light p-2 rounded-3 text-success-custom fw-bold">02</div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Batas Waktu Pengajuan</h6>
                                        <p class="text-muted small m-0">Proses *booking* instrumen dilakukan paling lambat H-1 sebelum tanggal penggunaan alat.</p>
                                    </div>
                                </div>
                                <hr class="my-2 text-muted opacity-25">

                                <div class="d-flex gap-3 align-items-start">
                                    <div class="bg-light p-2 rounded-3 text-success-custom fw-bold">03</div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Verifikasi Kartu Tanda Mahasiswa (KTM)</h6>
                                        <p class="text-muted small m-0">Saat melakukan pengambilan alat di laboratorium/studio, peminjam wajib menunjukkan KTM asli yang sesuai dengan akun peminjam.</p>
                                    </div>
                                </div>
                                <hr class="my-2 text-muted opacity-25">

                                <div class="d-flex gap-3 align-items-start">
                                    <div class="bg-light p-2 rounded-3 text-success-custom fw-bold">04</div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Tanggung Jawab Kondisi Alat</h6>
                                        <p class="text-muted small m-0">Peminjam wajib memeriksa kondisi fisik dan fungsi instrumen saat serah terima. Segala bentuk kerusakan yang ditemukan saat pengembalian akan menjadi tanggung jawab penuh peminjam.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card border-0 rounded-4 shadow-sm overflow-hidden h-100" style="background-color: #FFFDF3; border: 1px solid #FFEAA7 !important;">
                            <div class="p-4 bg-warning bg-opacity-10 border-bottom border-warning border-opacity-25 text-center py-4">
                                <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-clock-history fs-3 fw-bold"></i>
                                </div>
                                <h5 class="fw-bold text-dark m-0">Sanksi Keterlambatan</h5>
                            </div>

                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div class="text-center mb-4">
                                    <p class="text-muted small mb-1">Tarif denda keterlambatan pengembalian:</p>
                                    <h2 class="display-6 fw-extrabold text-danger m-0 font-monospace fw-bold">Rp 10.000</h2>
                                    <span class="badge bg-danger rounded-pill px-3 py-1 mt-2">Per Hari / Per Instrumen</span>
                                </div>

                                <div class="bg-white border rounded-3 p-3 shadow-sm small">
                                    <div class="d-flex gap-2 mb-2">
                                        <i class="bi bi-exclamation-circle-fill text-warning flex-shrink-0"></i>
                                        <span class="text-dark">Denda dihitung otomatis oleh sistem sejak tanggal jatuh tempo berakhir.</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-shield-x text-danger flex-shrink-0"></i>
                                        <span class="text-dark">Akun mahasiswa akan **ditangguhkan (suspended)** otomatis dan tidak bisa meminjam alat lagi sampai denda dilunasi.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                            <h4 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-left-right text-success-custom"></i> Alur Peminjaman & Pengembalian
                            </h4>

                            <div class="row g-3 timeline-steps">
                                <div class="col-md-3 text-center">
                                    <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                                        <i class="bi bi-laptop text-success-custom display-6 d-block mb-2"></i>
                                        <h6 class="fw-bold text-dark">1. Booking Online</h6>
                                        <p class="text-muted small m-0">Pilih instrumen di katalog, tentukan tanggal, lalu konfirmasi pesanan.</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="p-3 bg-light rounded-4 border h-100">
                                        <i class="bi bi-hourglass-split text-warning display-6 d-block mb-2"></i>
                                        <h6 class="fw-bold text-dark">2. Persetujuan Admin</h6>
                                        <p class="text-muted small m-0">Tunggu status berubah menjadi disetujui setelah diverifikasi oleh pihak admin.</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="p-3 bg-light rounded-4 border h-100">
                                        <i class="bi bi-handbag text-info display-6 d-block mb-2"></i>
                                        <h6 class="fw-bold text-dark">3. Pengambilan Alat</h6>
                                        <p class="text-muted small m-0">Bawa KTM ke studio musik untuk melakukan serah terima fisik instrumen.</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="p-3 bg-light rounded-4 border h-100">
                                        <i class="bi bi-arrow-counterclockwise text-success display-6 d-block mb-2"></i>
                                        <h6 class="fw-bold text-dark">4. Pengembalian Tepat Waktu</h6>
                                        <p class="text-muted small m-0">Kembalikan alat sesuai tanggal tenggat waktu untuk menghindari denda Rp 10.000/hari.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>