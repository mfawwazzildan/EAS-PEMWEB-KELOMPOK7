<?php
session_start();

if (!isset($_SESSION['login_user']) || !isset($_SESSION['level_user'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID Peminjaman tidak valid!'); window.location.href='history.php';</script>";
    exit;
}

$id_pinjam = $_GET['id'];

$sql = "SELECT peminjaman.*, 
               barang.nama_barang, 
               barang.tahun,
               kategori.nama_kategori,
               users.nama,
               users.email,
               users.telp,
               users.npm
        FROM peminjaman
        JOIN barang ON peminjaman.id_barang = barang.id_barang
        JOIN kategori ON barang.id_kategori = kategori.id_kategori
        JOIN users ON peminjaman.id_user = users.id_user
        WHERE peminjaman.id_pinjam = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_pinjam);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo "<script>alert('Data peminjaman tidak ditemukan!'); window.location.href='history.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($result);

if ($_SESSION['level_user'] === 'Mahasiswa' && $data['id_user'] != $_SESSION['id_user']) {
    echo "<script>alert('Anda tidak memiliki akses ke data ini!'); window.location.href='history.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Pengambilan Alat - <?= htmlspecialchars($data['kode_peminjaman']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #F8F9FA;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-ticket {
            background: #fff;
            border: 2px dashed #198754;
            border-radius: 16px;
            max-width: 650px;
            margin: 40px auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .ticket-header {
            background: linear-gradient(135deg, #198754, #146c43);
            color: #fff;
            padding: 24px;
            text-align: center;
            position: relative;
        }

        .ticket-header h4 {
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .ticket-body {
            padding: 30px;
        }

        .info-section h6 {
            color: #198754;
            font-weight: 700;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .barcode-box {
            border: 1px solid #dee2e6;
            background: #fdfdfd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .code-text {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: #212529;
        }

        .btn-action-container {
            max-width: 650px;
            margin: 0 auto 40px auto;
        }

        @media print {
            body {
                background-color: #fff;
            }

            .card-ticket {
                border: 2px solid #000;
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }

            .ticket-header {
                background: #198754 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .btn-action-container {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="container btn-action-container pt-4 text-center text-sm-start">
        <a href="history.php" class="btn btn-secondary rounded-pill px-4 shadow-sm me-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
        </a>
        <button onclick="window.print()" class="btn btn-success rounded-pill px-4 shadow-sm">
            <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
        </button>
    </div>

    <div class="container">
        <div class="card-ticket">

            <div class="ticket-header">
                <h4>KARTU PENGAMBILAN INSTRUMEN</h4>
                <p class="m-0 small opacity-75">UPNVJT Music Instrument Rental</p>
            </div>

            <div class="ticket-body">
                <div class="row g-4">

                    <div class="col-md-7">
                        <div class="info-section mb-4">
                            <h6><i class="bi bi-person-fill me-1"></i> Data Peminjam</h6>
                            <table class="table table-borderless table-sm m-0 small">
                                <tr>
                                    <td width="35%" class="text-secondary">Nama</td>
                                    <td>: <strong><?= htmlspecialchars($data['nama']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">NIM</td>
                                    <td>: <?= htmlspecialchars($data['npm'] ?? $_SESSION['id_user'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Prodi</td>
                                    <td>: <?= htmlspecialchars($data['email']) ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="info-section">
                            <h6><i class="bi bi-music-note-list me-1"></i> Detail Instrumen</h6>
                            <table class="table table-borderless table-sm m-0 small">
                                <tr>
                                    <td width="35%" class="text-secondary">Nama Alat</td>
                                    <td>: <strong><?= htmlspecialchars($data['nama_barang']) ?> (<?= $data['tahun'] ?>)</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Kategori</td>
                                    <td>: <?= htmlspecialchars($data['nama_kategori']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Jumlah Pinjam</td>
                                    <td>: <?= htmlspecialchars($data['jumlah_pinjam']) ?> Unit / Pcs</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Tgl Pengambilan</td>
                                    <td>: <?= date('d F Y', strtotime($data['tgl_mulai'])) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Batas Kembali</td>
                                    <td>: <strong class="text-success"><?= date('d F Y', strtotime($data['tgl_selesai'])) ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-5 d-flex flex-column justify-content-center border-start ps-md-4">
                        <div class="barcode-box">
                            <span class="text-secondary d-block small mb-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Kode Peminjaman</span>
                            <div class="code-text mb-3"><?= htmlspecialchars($data['kode_peminjaman']) ?></div>

                            <div class="text-center py-3 bg-light rounded border border-light-subtle">
                                <?php
                                $status_peminjaman = strtolower($data['status']);
                                $is_valid = ($status_peminjaman !== 'ditolak') ? 'VALID' : 'TIDAK VALID / DITOLAK';

                                $qr_data = "Kode: " . $data['kode_peminjaman'] . "\n" .
                                    "Peminjam: " . $data['nama'] . "\n" .
                                    "Status Data: " . $is_valid;

                                $qr_encoded = urlencode($qr_data);

                                $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $qr_encoded;
                                ?>

                                <img src="<?= $qr_api_url; ?>" alt="QR Code Peminjaman" class="img-fluid shadow-sm rounded bg-white p-2" style="max-width: 130px;">

                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">Tunjukkan kode atau kartu ini kepada petugas laboratorium musik saat mengambil alat.</small>
                        </div>
                    </div>

                </div>

                <hr class="my-4 text-black-50">
                <div class="alert alert-light border-0 m-0 p-2 small text-muted bg-light rounded-3" style="font-size: 0.75rem;">
                    <ol class="m-0 ps-3">
                        <li>Membawa KTM (Kartu Tanda Mahasiswa) asli saat verifikasi fisik.</li>
                        <li>Keterlambatan pengembalian akan dikenakan sanksi denda sesuai aturan yang berlaku.</li>
                        <li>Pastikan kondisi instrumen dicek bersama petugas sebelum meninggalkan ruangan.</li>
                    </ol>
                </div>
            </div>

        </div>
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>

</html>