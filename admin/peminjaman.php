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

if (isset($_GET['action']) && $_GET['action'] === 'export_excel') {
    $nama_file = 'DataPeminjaman_' . date('d-m-Y_H.i.s') . '.xls';

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$nama_file\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    $query_export = mysqli_query($conn, "SELECT 
                                            p.tgl_mulai, p.tgl_selesai, p.tgl_kembali, p.jumlah_pinjam, p.status,
                                            u.nama, u.npm, 
                                            b.nama_barang
                                         FROM peminjaman p
                                         INNER JOIN users u ON p.id_user = u.id_user
                                         INNER JOIN barang b ON p.id_barang = b.id_barang
                                         ORDER BY p.id_pinjam DESC");
?>
    <meta charset="UTF-8">
    <table border="1">
        <thead>
            <tr style="background-color: #198754; color: #ffffff; font-weight: bold; height: 30px;">
                <th style="padding: 5px 10px;">Nama Mahasiswa</th>
                <th style="padding: 5px 10px;">NPM</th>
                <th style="padding: 5px 10px;">Nama Instrumen</th>
                <th style="padding: 5px 10px;">Jumlah Pinjam</th>
                <th style="padding: 5px 10px;">Tgl Mulai</th>
                <th style="padding: 5px 10px;">Tgl Selesai</th>
                <th style="padding: 5px 10px;">Tgl Kembali</th>
                <th style="padding: 5px 10px;">Status Transaksi</th>
                <th style="padding: 5px 10px;">Status Terlambat</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($query_export)):
                // Logika pengecekan keterlambatan untuk file Excel
                $status_terlambat = '-';
                if ($row['status'] === 'dikembalikan' && !empty($row['tgl_kembali']) && !empty($row['tgl_selesai'])) {
                    $d1 = new DateTime($row['tgl_selesai']);
                    $d2 = new DateTime($row['tgl_kembali']);

                    if ($d2 > $d1) {
                        $diff = $d2->diff($d1);
                        $status_terlambat = 'Terlambat (' . $diff->days . ' Hari)';
                    } else {
                        $status_terlambat = 'Tepat Waktu';
                    }
                } elseif ($row['status'] === 'Dipinjam' && !empty($row['tgl_selesai'])) {
                    // Opsional: Cek jika sedang dipinjam tapi sudah melewati tgl_selesai hari ini
                    $d1 = new DateTime($row['tgl_selesai']);
                    $hari_ini = new DateTime(date('Y-m-d'));
                    if ($hari_ini > $d1) {
                        $diff = $hari_ini->diff($d1);
                        $status_terlambat = 'Belum Kembali (Terlambat ' . $diff->days . ' Hari)';
                    }
                }
            ?>
                <tr style="height: 25px;">
                    <td style="padding: 5px;"><?= htmlspecialchars($row['nama']); ?></td>
                    <td style="mso-number-format:'\@'; padding: 5px;"><?= htmlspecialchars($row['npm']); ?></td>
                    <td style="padding: 5px;"><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td style="padding: 5px; text-align: center;"><?= htmlspecialchars($row['jumlah_pinjam']); ?> unit</td>
                    <td style="padding: 5px; text-align: center;"><?= $row['tgl_mulai'] ? date('d-m-Y', strtotime($row['tgl_mulai'])) : '-'; ?></td>
                    <td style="padding: 5px; text-align: center;"><?= $row['tgl_selesai'] ? date('d-m-Y', strtotime($row['tgl_selesai'])) : '-'; ?></td>
                    <td style="padding: 5px; text-align: center;"><?= $row['tgl_kembali'] ? date('d-m-Y', strtotime($row['tgl_kembali'])) : '-'; ?></td>
                    <td style="padding: 5px;"><?= htmlspecialchars(ucfirst($row['status'])); ?></td>
                    <td style="padding: 5px; color: <?= strpos($status_terlambat, 'Terlambat') !== false ? '#dc3545' : '#198754'; ?>; font-weight: 500;">
                        <?= $status_terlambat; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php
    exit;
}

if (isset($_POST['update_status'])) {
    $id_pinjam   = $_POST['id_pinjam'];
    $status_baru = $_POST['status'];
    $tgl_kembali = !empty($_POST['tgl_kembali']) ? $_POST['tgl_kembali'] : null;

    $sql_cek  = "SELECT id_barang, jumlah_pinjam, status, tgl_selesai FROM peminjaman WHERE id_pinjam = ?";
    $stmt_cek = mysqli_prepare($conn, $sql_cek);
    mysqli_stmt_bind_param($stmt_cek, "i", $id_pinjam);
    mysqli_stmt_execute($stmt_cek);
    $res_cek  = mysqli_stmt_get_result($stmt_cek);
    $data_old = mysqli_fetch_assoc($res_cek);

    if ($data_old) {
        $id_barang   = $data_old['id_barang'];
        $qty         = $data_old['jumlah_pinjam'];
        $tgl_selesai = $data_old['tgl_selesai'];
        $status_lama       = $data_old['status'];
        $status_baru_match = $status_baru;

        mysqli_begin_transaction($conn);

        try {
            if ($status_baru_match === 'dikembalikan') {
                $sql_update  = "UPDATE peminjaman SET status = ?, tgl_kembali = ? WHERE id_pinjam = ?";
                $stmt_update = mysqli_prepare($conn, $sql_update);
                $stmt_update = mysqli_prepare($conn, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "ssi", $status_baru, $tgl_kembali, $id_pinjam);
            } else {
                $sql_update  = "UPDATE peminjaman SET status = ? WHERE id_pinjam = ?";
                $stmt_update = mysqli_prepare($conn, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "si", $status_baru, $id_pinjam);
            }
            mysqli_stmt_execute($stmt_update);

            if ($status_lama === 'Pending' && $status_baru_match === 'disetujui') {
                $sql_stok  = "UPDATE barang SET stok = stok - ? WHERE id_barang = ?";
                $stmt_stok = mysqli_prepare($conn, $sql_stok);
                mysqli_stmt_bind_param($stmt_stok, "ii", $qty, $id_barang);
                mysqli_stmt_execute($stmt_stok);
            } elseif ($status_lama === 'disetujui' && $status_baru_match === 'ditolak') {
                $sql_stok  = "UPDATE barang SET stok = stok + ? WHERE id_barang = ?";
                $stmt_stok = mysqli_prepare($conn, $sql_stok);
                mysqli_stmt_bind_param($stmt_stok, "ii", $qty, $id_barang);
                mysqli_stmt_execute($stmt_stok);
            } elseif ($status_lama === 'Dipinjam' && $status_baru_match === 'dikembalikan') {
                $sql_stok  = "UPDATE barang SET stok = stok + ? WHERE id_barang = ?";
                $stmt_stok = mysqli_prepare($conn, $sql_stok);
                mysqli_stmt_bind_param($stmt_stok, "ii", $qty, $id_barang);
                mysqli_stmt_execute($stmt_stok);

                if (!empty($tgl_kembali) && !empty($tgl_selesai)) {
                    $d1 = new DateTime($tgl_selesai);
                    $d2 = new DateTime($tgl_kembali);

                    if ($d2 > $d1) {
                        $diff = $d2->diff($d1);
                        $hari_terlambat = $diff->days;
                        $tarif_per_hari = 10000;
                        $total_denda    = $hari_terlambat * $tarif_per_hari;

                        $sql_cek_denda  = "SELECT id_denda FROM denda WHERE id_pinjam = ?";
                        $stmt_cek_denda = mysqli_prepare($conn, $sql_cek_denda);
                        mysqli_stmt_bind_param($stmt_cek_denda, "i", $id_pinjam);
                        mysqli_stmt_execute($stmt_cek_denda);
                        mysqli_stmt_store_result($stmt_cek_denda);

                        if (mysqli_stmt_num_rows($stmt_cek_denda) == 0) {
                            $sql_denda  = "INSERT INTO denda (id_pinjam, jumlah_denda, status_denda) VALUES (?, ?, 'Belum Lunas')";
                            $stmt_denda = mysqli_prepare($conn, $sql_denda);
                            mysqli_stmt_bind_param($stmt_denda, "ii", $id_pinjam, $total_denda);
                            mysqli_stmt_execute($stmt_denda);
                        }
                    }
                }
            }
            mysqli_commit($conn);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "Gagal memproses pengembalian: " . $e->getMessage();
            exit;
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kelola Peminjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <div class="admin-layout">
        <?php include 'assets/navbar.php'; ?>

        <main class="content-admin">
            <div class="topbar-admin">
                <div>
                    <h1>Data Peminjaman Instrumen</h1>
                </div>
                <div class="top-buttons">
                    <a href="?action=export_excel" class="btn btn-export text-decoration-none d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                </div>
            </div>

            <div class="table-container">
                <div class="table-card">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Kode Peminjaman</th>
                                <th>Mahasiswa</th>
                                <th>Instrumen</th>
                                <th>Tgl Mulai</th>
                                <th>Tgl Selesai</th>
                                <th>Tgl Kembali</th>
                                <th>Jumlah Pinjam</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query_peminjaman = "SELECT 
                                                    p.id_pinjam,p.kode_peminjaman, p.status, p.tgl_mulai, p.tgl_selesai, p.tgl_kembali, p.jumlah_pinjam, p.catatan,
                                                    u.nama, u.npm, 
                                                    b.nama_barang, b.id_barang
                                                 FROM peminjaman p
                                                 INNER JOIN users u ON p.id_user = u.id_user
                                                 INNER JOIN barang b ON p.id_barang = b.id_barang
                                                 ORDER BY p.id_pinjam DESC";

                            $run_query = mysqli_query($conn, $query_peminjaman);
                            while ($data = mysqli_fetch_assoc($run_query)) {
                                $badge_color = 'bg-warning text-dark';
                                if ($data['status'] === 'disetujui') $badge_color = 'bg-primary text-white';
                                if ($data['status'] === 'Dipinjam') $badge_color = 'bg-info text-white';
                                if ($data['status'] === 'dikembalikan') $badge_color = 'bg-success text-white';
                                if ($data['status'] === 'Ditolak') $badge_color = 'bg-danger text-white';
                            ?>
                                <tr>
                                    <td><strong><?= $data['kode_peminjaman']; ?></strong></td>
                                    <td><?= htmlspecialchars($data['nama']); ?></td>
                                    <td><?= htmlspecialchars($data['nama_barang']); ?></td>
                                    <td><?= $data['tgl_mulai'] ? $data['tgl_mulai'] : '<span class="text-muted small"><i>Kosong</i></span>'; ?></td>
                                    <td><?= $data['tgl_selesai'] ? $data['tgl_selesai'] : '<span class="text-muted small"><i>Kosong</i></span>'; ?></td>
                                    <td><?= $data['tgl_kembali'] ? $data['tgl_kembali'] : '<span class="text-muted small"><i>Kosong</i></span>'; ?></td>
                                    <td><?= $data['jumlah_pinjam']; ?> unit</td>
                                    <td>
                                        <span class="badge <?= $badge_color; ?> rounded-pill px-3 py-1">
                                            <?= ucfirst($data['status']); ?>
                                        </span>
                                    </td>
                                    <td class="d-flex gap-2">
                                        <?php if ($data['status'] === 'dikembalikan' || $data['status'] === 'ditolak'): ?>
                                            <button class="btn-success-action opacity-50" disabled style="cursor: not-allowed;" title="Transaksi telah selesai">
                                                <i class="bi bi-lock-fill"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-success-action" data-bs-toggle="modal" data-bs-target="#editStatus<?= $data['id_pinjam']; ?>">
                                                <i class="bi bi-gear-fill"></i>
                                            </button>
                                        <?php endif; ?>

                                        <button class="btn-danger-action" data-bs-toggle="modal" data-bs-target="#hapusLog<?= $data['id_pinjam']; ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editStatus<?= $data['id_pinjam']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Peninjauan Peminjaman</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <input type="hidden" name="id_pinjam" value="<?= $data['id_pinjam']; ?>">
                                                    <input type="hidden" name="status" id="status_aksi<?= $data['id_pinjam']; ?>" value="">

                                                    <div class="alert alert-light border mb-3 text-start">
                                                        <small class="text-muted d-block">Peminjam:</small>
                                                        <strong><?= htmlspecialchars($data['nama']); ?></strong>
                                                        <small class="text-muted d-block mt-2">NPM:</small>
                                                        <strong><?= htmlspecialchars($data['npm']); ?></strong>
                                                        <small class="text-muted d-block mt-2">Instrumen & Jumlah:</small>
                                                        <strong><?= htmlspecialchars($data['nama_barang']); ?> (<?= $data['jumlah_pinjam']; ?> unit)</strong>
                                                        <small class="text-muted d-block mt-2">Tanggal Awal:</small>
                                                        <strong><?= htmlspecialchars($data['tgl_mulai']); ?></strong>
                                                        <small class="text-muted d-block mt-2">Tanggal Selesai:</small>
                                                        <strong><?= htmlspecialchars($data['tgl_selesai']); ?></strong>
                                                    </div>

                                                    <?php if ($data['status'] === 'Pending'): ?>
                                                        <div class="mb-4 text-start">
                                                            <label class="form-label fw-semibold text-secondary small">Keperluan Peminjaman</label>
                                                            <textarea readonly class="form-control"><?= htmlspecialchars($data['catatan']); ?></textarea>
                                                        </div>

                                                        <div class="d-flex flex-column gap-2">
                                                            <button type="submit" name="update_status" class="btn btn-success py-2 fw-semibold"
                                                                onclick="document.getElementById('status_aksi<?= $data['id_pinjam']; ?>').value='disetujui';">
                                                                <i class="bi bi-check-circle me-2"></i> Setujui Pengajuan
                                                            </button>
                                                            <button type="submit" name="update_status" class="btn btn-outline-danger py-2 fw-semibold"
                                                                onclick="if(confirm('Yakin ingin menolak pengajuan ini?')) { document.getElementById('status_aksi<?= $data['id_pinjam']; ?>').value='Ditolak'; } else { return false; }">
                                                                <i class="bi bi-x-circle me-2"></i> Tolak Peminjaman
                                                            </button>
                                                        </div>

                                                    <?php elseif ($data['status'] === 'disetujui'): ?>
                                                        <div class="alert alert-info text-start small mb-4">
                                                            <i class="bi bi-info-circle-fill me-2"></i> Pengajuan telah disetujui. Klik tombol di bawah jika mahasiswa sudah datang mengambil instrumen.
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <button type="submit" name="update_status" class="btn btn-info text-white py-2 fw-semibold"
                                                                onclick="document.getElementById('status_aksi<?= $data['id_pinjam']; ?>').value='dipinjam';">
                                                                <i class="bi bi-play-fill me-2"></i> Serahkan Barang (Mulai Dipinjam)
                                                            </button>
                                                        </div>

                                                    <?php elseif ($data['status'] === 'dipinjam'): ?>
                                                        <div class="mb-4 text-start">
                                                            <label class="form-label fw-semibold text-secondary small">Tanggal Pengembalian Riil</label>
                                                            <input type="date" name="tgl_kembali" class="form-control" required value="<?= date('Y-m-d'); ?>">
                                                        </div>

                                                        <div class="d-flex flex-column">
                                                            <button type="submit" name="update_status" class="btn btn-primary py-2 fw-semibold"
                                                                onclick="document.getElementById('status_aksi<?= $data['id_pinjam']; ?>').value='dikembalikan';">
                                                                <i class="bi bi-arrow-counterclockwise me-2"></i> Konfirmasi Pengembalian Barang
                                                            </button>
                                                        </div>

                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="hapusLog<?= $data['id_pinjam']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4">
                                            <form method="POST">
                                                <div class="modal-body text-center p-4">
                                                    <input type="hidden" name="id" value="<?= $data['id_pinjam']; ?>">
                                                    <i class="bi bi-trash-fill text-danger fs-1"></i>
                                                    <h4 class="mt-3 fw-bold">Hapus Riwayat Log?</h4>
                                                    <p class="text-muted">Data log riwayat transaksi peminjaman ini akan dihapus permanen dari database.</p>
                                                    <div class="d-flex gap-2 mt-4">
                                                        <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="hapus" class="btn btn-danger w-100">Hapus</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>