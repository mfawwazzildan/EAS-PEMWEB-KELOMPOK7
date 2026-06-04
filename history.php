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

$id_user_login = $_SESSION['id_user']; 
$hari_ini = date('Y-m-d');

$query_total = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM peminjaman WHERE id_user = ?");
mysqli_stmt_bind_param($query_total, "s", $id_user_login);
mysqli_stmt_execute($query_total);
$total_all = mysqli_fetch_assoc(mysqli_stmt_get_result($query_total))['total'];

$sql_total_denda = "SELECT SUM(denda.jumlah_denda) AS total_denda 
                    FROM denda 
                    JOIN peminjaman ON denda.id_pinjam = peminjaman.id_pinjam 
                    WHERE peminjaman.id_user = ? AND LOWER(denda.status_denda) = 'belum lunas'";
$query_denda = mysqli_prepare($conn, $sql_total_denda);
mysqli_stmt_bind_param($query_denda, "s", $id_user_login);
mysqli_stmt_execute($query_denda);
$res_total_denda = mysqli_fetch_assoc(mysqli_stmt_get_result($query_denda));
$total_denda_mahasiswa = $res_total_denda['total_denda'] ?? 0;


$status_filter = isset($_GET['tab']) ? $_GET['tab'] : 'semua';

$sql_history = "SELECT peminjaman.*, 
                        barang.nama_barang, 
                        barang.gambar, 
                        barang.tahun, 
                        kategori.nama_kategori,
                        denda.jumlah_denda AS nilai_denda,
                        denda.status_denda
                FROM peminjaman 
                JOIN barang ON peminjaman.id_barang = barang.id_barang 
                JOIN kategori ON barang.id_kategori = kategori.id_kategori 
                LEFT JOIN denda ON peminjaman.id_pinjam = denda.id_pinjam
                WHERE peminjaman.id_user = ?";

if ($status_filter === 'pending') {
    $sql_history .= " AND LOWER(peminjaman.status) = 'pending'";
} elseif ($status_filter === 'disetujui') {
    $sql_history .= " AND LOWER(peminjaman.status) = 'disetujui'";
} elseif ($status_filter === 'ditolak') {
    $sql_history .= " AND LOWER(peminjaman.status) = 'ditolak'";
} elseif ($status_filter === 'aktif') {
    $sql_history .= " AND LOWER(peminjaman.status) = 'dipinjam' AND peminjaman.tgl_selesai >= '$hari_ini'";
} elseif ($status_filter === 'selesai') {
    $sql_history .= " AND (LOWER(peminjaman.status) = 'dikembalikan' OR LOWER(peminjaman.status) = 'kembali')";
} elseif ($status_filter === 'terlambat') {
    // Definisi terlambat: datanya eksis/tercatat di tabel denda
    $sql_history .= " AND denda.id_pinjam IS NOT NULL";
}

$sql_history .= " ORDER BY peminjaman.id_pinjam DESC";

$query_history = mysqli_prepare($conn, $sql_history);
mysqli_stmt_bind_param($query_history, "s", $id_user_login);
mysqli_stmt_execute($query_history);
$res_history = mysqli_stmt_get_result($query_history);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPNVJT Music - Riwayat Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">

    <style>
        .nav-tabs-custom {
            border-bottom: 1px solid #dee2e6;
            gap: 5px;
            flex-wrap: wrap;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6c757d;
            padding: 8px 14px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .nav-tabs-custom .nav-link.active {
            color: #198754;
            border-bottom: 3px solid #198754;
            font-weight: 600;
            background: none;
        }

        .history-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .history-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .instrument-img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
        }

        .badge-status {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            text-transform: capitalize;
            display: inline-block;
        }

        .badge-pending { background-color: #E2E3E5; color: #41464B; }
        .badge-disetujui { background-color: #CFF4FC; color: #055160; }
        .badge-ditolak { background-color: #F8D7DA; color: #842029; }
        .badge-dipinjam { background-color: #FFF3CD; color: #856404; }
        .badge-dikembalikan, .badge-kembali { background-color: #D1E7DD; color: #0F5132; }
        .badge-terlambat { background-color: #F8D7DA; color: #842029; border: 1px dashed #842029; }
        
        .badge-denda { font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; }
        .badge-lunas { background-color: #D1E7DD; color: #0F5132; }
        .badge-belum-lunas { background-color: #F8D7DA; color: #842029; }

        .bg-lightblue { background-color: #F0F4F8; }
    </style>
</head>

<body>
    <div class="container-fluid pt-5 mt-4">
        <div class="row">
            <?php include 'assets/navbar.php' ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4 bg-content min-vh-100">

                <div class="mb-4">
                    <h1 class="h2 fw-bold text-success-custom mb-1">Riwayat Peminjaman</h1>
                    <p class="text-muted small">Pantau status peminjaman instrumen musik Anda secara real-time.</p>
                </div>

                <div class="stats-grid mb-4">
                    <div class="stats-card green">
                        <div>
                            <span>Total Pinjam</span>
                            <h2><?= $total_all ?></h2>
                            <small>Seluruh riwayat</small>
                        </div>
                        <i class="bi bi-vinyl-fill"></i>
                    </div>

                    <div class="stats-card yellow">
                        <div>
                            <span>Denda Perlu Dibayar</span>
                            <h2>Rp. <?= number_format($total_denda_mahasiswa, 0, ',', '.') ?></h2>
                            <small>Akumulasi denda belum lunas</small>
                        </div>
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>

                <ul class="nav nav-tabs-custom mb-4">
                    <li class="nav-item"><a class="nav-link <?= $status_filter === 'semua' ? 'active' : '' ?>" href="history.php?tab=semua">Semua</a></li>
                    <li class="nav-item"><a class="nav-link <?= $status_filter === 'pending' ? 'active' : '' ?>" href="history.php?tab=pending">Pending</a></li>
                    <li class="nav-item"><a class="nav-link <?= $status_filter === 'disetujui' ? 'active' : '' ?>" href="history.php?tab=disetujui">Disetujui</a></li>
                    <li class="nav-item"><a class="nav-link <?= $status_filter === 'ditolak' ? 'active' : '' ?>" href="history.php?tab=ditolak">Ditolak</a></li>
                    <li class="nav-item"><a class="nav-link <?= $status_filter === 'aktif' ? 'active' : '' ?>" href="history.php?tab=aktif">Aktif</a></li>
                    <li class="nav-item"><a class="nav-link <?= $status_filter === 'selesai' ? 'active' : '' ?>" href="history.php?tab=selesai">Selesai</a></li>
                    <li class="nav-item"><a class="nav-link <?= $status_filter === 'terlambat' ? 'active' : '' ?>" href="history.php?tab=terlambat">Terlambat</a></li>
                </ul>

                <div class="history-list-wrapper">
                    <?php if (mysqli_num_rows($res_history) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_history)):
                            $db_status = strtolower($row['status']);
                            
                            // Mengambil data denda yang di-join langsung dari database
                            $nilai_denda = $row['nilai_denda'] ?? 0;
                            $db_status_denda = !empty($row['status_denda']) ? strtolower($row['status_denda']) : '';

                            // Flag terlambat jika baris memiliki rekaman denda
                            $is_terlambat = ($nilai_denda > 0); 
                            $status_denda_teks = "Tanpa Denda";

                            if ($is_terlambat) {
                                $status_denda_teks = ($db_status_denda === 'lunas') ? 'Lunas' : 'Belum Dibayar';
                            }

                            // Penentuan badge utama tampilan card instrumen
                            if ($is_terlambat) {
                                $status_text = 'Terlambat';
                                $status_label = 'terlambat';
                            } else {
                                switch ($db_status) {
                                    case 'disetujui': $status_text = 'Disetujui'; $status_label = 'disetujui'; break;
                                    case 'ditolak': $status_text = 'Ditolak'; $status_label = 'ditolak'; break;
                                    case 'dipinjam': $status_text = 'Dipinjam'; $status_label = 'dipinjam'; break;
                                    case 'dikembalikan':
                                    case 'kembali': $status_text = 'Dikembalikan'; $status_label = 'dikembalikan'; break;
                                    default: $status_text = 'Pending'; $status_label = 'pending'; break;
                                }
                            }
                        ?>
                            <div class="history-card">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="admin/img_barang/<?= $row['gambar'] ?>" class="instrument-img border" alt="Instrumen">

                                    <div>
                                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($row['nama_barang']) ?>, <?= htmlspecialchars($row['tahun']) ?></h5>
                                        
                                        <?php if ($nilai_denda > 0): ?>
                                            <div class="mb-2">
                                                <span class="badge bg-secondary small me-1">Denda: Rp <?= number_format($nilai_denda, 0, ',', '.') ?></span>
                                                <span class="badge-denda <?= $status_denda_teks === 'Lunas' ? 'badge-lunas' : 'badge-belum-lunas' ?>">
                                                    <i class="bi <?= $status_denda_teks === 'Lunas' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i>Denda <?= $status_denda_teks ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex flex-wrap gap-4 text-muted small">
                                            <div>
                                                <span class="d-block text-secondary" style="font-size: 0.75rem;">Tanggal Pinjam</span>
                                                <strong class="text-dark"><?= date('d M Y', strtotime($row['tgl_mulai'])) ?></strong>
                                            </div>
                                            <div>
                                                <span class="d-block text-secondary" style="font-size: 0.75rem;">Batas Kembali</span>
                                                <strong class="text-success"><?= date('d M Y', strtotime($row['tgl_selesai'])) ?></strong>
                                            </div>
                                            <?php if (!empty($row['tgl_kembali']) && $row['tgl_kembali'] !== '0000-00-00'): ?>
                                            <div>
                                                <span class="d-block text-secondary" style="font-size: 0.75rem;">Tanggal Pengembalian</span>
                                                <strong class="text-primary"><?= date('d M Y', strtotime($row['tgl_kembali'])) ?></strong>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge-status badge-<?= $status_label ?>"><?= $status_text ?></span>

                                    <button type="button"
                                        class="btn btn-outline-secondary btn-sm px-3 rounded-2 btn-detail-modal"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetailHistory"
                                        data-id_pinjam="<?= $row['id_pinjam'] ?>"
                                        data-id="<?= $row['kode_peminjaman'] ?>"
                                        data-nama="<?= htmlspecialchars($row['nama_barang']) ?>"
                                        data-tahun="<?= $row['tahun'] ?>"
                                        data-kategori="<?= htmlspecialchars($row['nama_kategori'] ?? 'Instrumen Musik') ?>"
                                        data-gambar="admin/img_barang/<?= $row['gambar'] ?>"
                                        data-jumlah="<?= $row['jumlah_pinjam'] ?>"
                                        data-mulai="<?= date('d F Y', strtotime($row['tgl_mulai'])) ?>"
                                        data-selesai="<?= date('d F Y', strtotime($row['tgl_selesai'])) ?>"
                                        data-catatan="<?= htmlspecialchars($row['catatan'] ?? 'Tidak ada catatan keperluan.') ?>"
                                        data-status="<?= $status_text ?>"
                                        data-statusclass="badge-<?= $status_label ?>"
                                        data-denda="<?= $nilai_denda > 0 ? 'Rp ' . number_format($nilai_denda, 0, ',', '.') : 'Rp 0' ?>"
                                        data-statusdenda="<?= $status_denda_teks ?>">
                                        Detail
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 bg-white rounded-4 border">
                            <i class="bi bi-folder-x fs-1 text-muted"></i>
                            <p class="text-muted mt-2">Belum ada riwayat peminjaman instrumen untuk kategori ini.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>

    <div class="modal fade" id="modalDetailHistory" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
            <div class="modal-content border-0 rounded-4 overflow-hidden" style="background-color: #F8F9FA;">

                <div class="modal-header border-0 px-4 py-3 bg-lightblue">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn border-0 p-0 m-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-arrow-left fs-4 text-success-custom"></i>
                        </button>
                        <h5 class="modal-title fw-bold m-0 fs-5 text-success-custom" id="modalDetailLabel">Detail Peminjaman</h5>
                    </div>
                </div>

                <div class="modal-body p-4">
                    <div class="card border-light-subtle rounded-4 p-3 shadow-sm bg-white mb-4">
                        <div class="d-flex gap-3 align-items-center">
                            <img id="detail-img" src="" alt="Gambar" class="rounded-3 border object-fit-cover" style="width: 75px; height: 75px;">
                            <div>
                                <h6 id="detail-nama" class="fw-bold text-dark m-0 fs-5">Nama Barang</h6>
                                <p id="detail-kategori" class="text-muted small m-0 mb-2">Kategori</p>
                                <span id="detail-badge" class="badge-status">Status</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3 bg-white p-3 rounded-4 border border-light-subtle shadow-sm mb-3">
                        <div>
                            <label class="text-secondary small d-block mb-1">ID Peminjaman</label>
                            <strong id="detail-id" class="text-dark"></strong>
                        </div>
                        <hr class="m-0 text-black-50">
                        <div>
                            <label class="text-secondary small d-block mb-1">Jumlah Alat Yang Dipinjam</label>
                            <strong id="detail-jumlah" class="text-dark">1 Pcs</strong>
                        </div>
                        <hr class="m-0 text-black-50">
                        <div>
                            <label class="text-secondary small d-block mb-1">Tanggal Mulai Pinjam</label>
                            <strong id="detail-mulai" class="text-dark">-</strong>
                        </div>
                        <hr class="m-0 text-black-50">
                        <div>
                            <label class="text-secondary small d-block mb-1">Batas Maksimal Pengembalian</label>
                            <strong id="detail-selesai" class="text-success">-</strong>
                        </div>
                        <hr class="m-0 text-black-50">
                        <div>
                            <label class="text-secondary small d-block mb-1">Informasi Nominal & Status Denda</label>
                            <strong id="detail-denda-info" class="text-danger">-</strong> 
                            <span id="detail-denda-status" class="badge ms-2"></span>
                        </div>
                    </div>

                    <div class="bg-white p-3 rounded-4 border border-light-subtle shadow-sm">
                        <label class="text-secondary small d-block mb-1 fw-bold">Keperluan / Catatan Mahasiswa:</label>
                        <p id="detail-catatan" class="text-muted m-0 small" style="line-height: 1.5;"></p>
                    </div>

                    <div id="note-pending-container" class="mt-3 d-none">
                        <div class="alert alert-warning border-0 rounded-3 d-flex align-items-start gap-2 m-0 p-3" style="background-color: #FFF9E6;">
                            <i class="bi bi-info-circle-fill text-warning fs-5" style="line-height: 1;"></i>
                            <div class="small text-dark">
                                <strong>Catatan:</strong> Silakan tunggu sampai disetujui oleh admin untuk dapat mengambil instrumen.
                            </div>
                        </div>
                    </div>

                    <div id="action-disetujui-container" class="mt-4 d-none">
                        <a id="link-cetak-kartu" href="#" class="btn btn-primary w-100 py-2.5 rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-card-checklist me-2"></i>Cetak Kartu Pengambilan
                        </a>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary w-100 py-2.5 rounded-pill fw-bold" data-bs-dismiss="modal">Tutup Detail</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tombolDetail = document.querySelectorAll(".btn-detail-modal");

            tombolDetail.forEach(btn => {
                btn.addEventListener("click", function() {
                    const idPinjam = this.getAttribute("data-id_pinjam");
                    const id = this.getAttribute("data-id");
                    const nama = this.getAttribute("data-nama");
                    const tahun = this.getAttribute("data-tahun");
                    const kategori = this.getAttribute("data-kategori");
                    const gambar = this.getAttribute("data-gambar");
                    const jumlah = this.getAttribute("data-jumlah");
                    const mulai = this.getAttribute("data-mulai");
                    const selesai = this.getAttribute("data-selesai");
                    const catatan = this.getAttribute("data-catatan");
                    const statusText = this.getAttribute("data-status");
                    const statusClass = this.getAttribute("data-statusclass");
                    const dendaNominal = this.getAttribute("data-denda");
                    const statusDenda = this.getAttribute("data-statusdenda");

                    document.getElementById("detail-id").textContent = `${id}`;
                    document.getElementById("detail-nama").textContent = `${nama}, ${tahun}`;
                    document.getElementById("detail-kategori").textContent = kategori;
                    document.getElementById("detail-img").setAttribute("src", gambar);
                    document.getElementById("detail-jumlah").textContent = `${jumlah} Unit / Pcs`;
                    document.getElementById("detail-mulai").textContent = mulai;
                    document.getElementById("detail-selesai").textContent = selesai;
                    document.getElementById("detail-catatan").textContent = catatan;

                    // Tampilkan info denda di dalam modal
                    const dendaTextEl = document.getElementById("detail-denda-info");
                    const dendaBadgeEl = document.getElementById("detail-denda-status");
                    
                    dendaTextEl.textContent = dendaNominal;
                    
                    if (statusDenda === 'Lunas') {
                        dendaBadgeEl.textContent = "Lunas";
                        dendaBadgeEl.className = "badge bg-success";
                        dendaTextEl.className = "text-success"; 
                    } else if (statusDenda === 'Belum Dibayar') {
                        dendaBadgeEl.textContent = "Belum Lunas";
                        dendaBadgeEl.className = "badge bg-danger";
                        dendaTextEl.className = "text-danger"; 
                    } else {
                        dendaBadgeEl.textContent = "Bebas Denda";
                        dendaBadgeEl.className = "badge bg-secondary";
                        dendaTextEl.className = "text-secondary";
                    }

                    const noteContainer = document.getElementById("note-pending-container");
                    const disetujuiContainer = document.getElementById("action-disetujui-container");
                    const linkCetakKartu = document.getElementById("link-cetak-kartu");

                    if (statusText.toLowerCase() === 'pending') {
                        noteContainer.classList.remove("d-none");
                        disetujuiContainer.classList.add("d-none");
                    } else if (statusText.toLowerCase() === 'disetujui') {
                        noteContainer.classList.add("d-none");
                        disetujuiContainer.classList.remove("d-none");
                        linkCetakKartu.setAttribute("href", `cetak_kartu.php?id=${idPinjam}`);
                    } else {
                        noteContainer.classList.add("d-none");
                        disetujuiContainer.classList.add("d-none");
                    }

                    const badge = document.getElementById("detail-badge");
                    badge.textContent = statusText;
                    badge.className = `badge-status ${statusClass}`;
                });
            });
        });
    </script>
</body>
</html>