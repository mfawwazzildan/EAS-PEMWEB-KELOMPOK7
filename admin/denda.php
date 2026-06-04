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

if (isset($_POST['bayar_denda'])) {
    $id_pinjam = $_POST['id_pinjam'];
    
    mysqli_begin_transaction($conn);
    try {
        $sql_update = "UPDATE denda SET status_denda = 'Lunas' WHERE id_pinjam = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "i", $id_pinjam);
        mysqli_stmt_execute($stmt_update);

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function hitungDendaPeminjaman($tgl_selesai, $tgl_kembali) {
    if (!$tgl_kembali || !$tgl_selesai) return 0;

    $selesai = new DateTime($tgl_selesai);
    $kembali = new DateTime($tgl_kembali);

    if ($kembali <= $selesai) return 0;

    $denda_total = 10000; 

    $hari_ini = new DateTime(date('Y-m-d'));
    if ($hari_ini > $kembali) {
        $selisih_bayar = $kembali->diff($hari_ini)->days;
        $tambahan_denda = floor($selisih_bayar / 3) * 10000;
        $denda_total += $tambahan_denda;
    }

    return $denda_total;
}

if (isset($_GET['action']) && $_GET['action'] === 'export_excel') {

    $nama_file = 'DataDenda_' . date('d-m-Y_H.i.s') . '.xls';

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$nama_file\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    $query_export = mysqli_query($conn, "
        SELECT
            d.id_denda,
            d.jumlah_denda,
            d.status_denda,

            u.nama,
            u.npm,

            b.nama_barang,

            p.jumlah_pinjam,
            p.tgl_mulai,
            p.tgl_selesai,
            p.tgl_kembali

        FROM denda d
        INNER JOIN peminjaman p
            ON d.id_pinjam = p.id_pinjam
        INNER JOIN users u
            ON p.id_user = u.id_user
        INNER JOIN barang b
            ON p.id_barang = b.id_barang

        ORDER BY d.id_denda DESC
    ");
?>
<meta charset="UTF-8">

<table border="1">
    <thead>
        <tr style="background-color:#198754;color:white;font-weight:bold;">
            <th>Nama Mahasiswa</th>
            <th>NPM</th>
            <th>Barang Dipinjam</th>
            <th>Jumlah Pinjam</th>
            <th>Tanggal Pinjam</th>
            <th>Tanggal Harus Kembali</th>
            <th>Tanggal Dikembalikan</th>
            <th>Jumlah Denda</th>
            <th>Status Denda</th>
        </tr>
    </thead>

    <tbody>
        <?php while ($row = mysqli_fetch_assoc($query_export)) : ?>
            <tr>
                <td><?= htmlspecialchars($row['nama']); ?></td>

                <td style="mso-number-format:'\@';">
                    <?= htmlspecialchars($row['npm']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['nama_barang']); ?>
                </td>

                <td style="text-align:center;">
                    <?= $row['jumlah_pinjam']; ?>
                </td>

                <td style="text-align:center;">
                    <?= !empty($row['tgl_mulai']) ? date('d-m-Y', strtotime($row['tgl_mulai'])) : '-'; ?>
                </td>

                <td style="text-align:center;">
                    <?= !empty($row['tgl_selesai']) ? date('d-m-Y', strtotime($row['tgl_selesai'])) : '-'; ?>
                </td>

                <td style="text-align:center;">
                    <?= !empty($row['tgl_kembali']) ? date('d-m-Y', strtotime($row['tgl_kembali'])) : '-'; ?>
                </td>

                <td style="text-align:right;">
                    Rp <?= number_format($row['jumlah_denda'], 0, ',', '.'); ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['status_denda']); ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kelola Denda</title>
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
                    <h1>Kelola Denda Mahasiswa</h1>
                    <p class="text-muted small">Daftar mahasiswa yang terkena denda keterlambatan pengembalian instrumen.</p>
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
                                <th>Kode_Peminjaman</th>
                                <th>Mahasiswa</th>
                                <th>NPM</th>
                                <th>Instrumen</th>
                                <th>Tgl Selesai</th>
                                <th>Tgl Kembali</th>
                                <th>Keterlambatan</th>
                                <th>Total Denda</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // PERBAIKAN: Query mengambil data master dari tabel denda, lalu di-JOIN ke peminjaman, users, dan barang
                            $query_denda = "SELECT 
                                                d.id_denda, d.jumlah_denda, d.status_denda,
                                                p.id_pinjam, p.tgl_selesai, p.tgl_kembali, p.kode_peminjaman,
                                                u.nama, u.npm, 
                                                b.nama_barang
                                             FROM denda d
                                             INNER JOIN peminjaman p ON d.id_pinjam = p.id_pinjam
                                             INNER JOIN users u ON p.id_user = u.id_user
                                             INNER JOIN barang b ON p.id_barang = b.id_barang
                                             ORDER BY d.id_denda DESC";

                            $run_query = mysqli_query($conn, $query_denda);
                            
                            if (mysqli_num_rows($run_query) == 0) {
                                echo '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data denda keterlambatan saat ini.</td></tr>';
                            }

                            while ($data = mysqli_fetch_assoc($run_query)) {
                                // Mengambil data denda riil yang tersimpan di database. Jika 0, gunakan fungsi kalkulasi dinamis.
                                $total_denda = ($data['jumlah_denda'] > 0) ? $data['jumlah_denda'] : hitungDendaPeminjaman($data['tgl_selesai'], $data['tgl_kembali']);
                                
                                // Hitung total hari telat mengembalikan
                                $selesai = new DateTime($data['tgl_selesai']);
                                $kembali = new DateTime($data['tgl_kembali']);
                                $selisih_telat = $selesai->diff($kembali)->days;

                                // PERBAIKAN: Menentukan status kelulusan berdasarkan kolom status_denda dari tabel denda
                                $is_lunas = (strtolower($data['status_denda']) === 'lunas'); 
                                $badge_color = $is_lunas ? 'bg-success text-white' : 'bg-danger text-white';
                                $status_teks = $is_lunas ? 'Lunas' : 'Belum Dibayar';
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= $data['kode_peminjaman']; ?></strong>
                                    </td>
                                    <td>
                                       <?= htmlspecialchars($data['nama']); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($data['npm']); ?>
                                    </td>
                                    <td><?= htmlspecialchars($data['nama_barang']); ?></td>
                                    <td><?= $data['tgl_selesai']; ?></td>
                                    <td><?= $data['tgl_kembali']; ?></td>
                                    <td><strong class="text-danger"><?= $selisih_telat; ?> Hari</strong></td>
                                    <td><strong class="text-primary">Rp <?= number_format($total_denda, 0, ',', '.'); ?></strong></td>
                                    <td>
                                        <span class="badge <?= $badge_color; ?> rounded-pill px-3 py-1">
                                            <?= $status_teks; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($is_lunas): ?>
                                            <button class="btn btn-sm btn-secondary opacity-50" disabled style="cursor: not-allowed;">
                                                <i class="bi bi-check-all"></i> Selesai
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-warning fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#bayarDenda<?= $data['id_pinjam']; ?>">
                                                <i class="bi bi-cash-coin me-1"></i> Bayar
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <div class="modal fade" id="bayarDenda<?= $data['id_pinjam']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Konfirmasi Pelunasan Denda</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4 text-start">
                                                    <input type="hidden" name="id_pinjam" value="<?= $data['id_pinjam']; ?>">
                                                    
                                                    <div class="alert alert-warning border mb-3">
                                                        <small class="text-muted d-block">Kode Peminajamn:</small>
                                                        <strong><?= $data['kode_peminjaman']; ?> </strong>
                                                        
                                                        <small class="text-muted d-block mt-2">Nama Mahasiswa:</small>
                                                        <?= htmlspecialchars($data['nama']); ?> (<?= htmlspecialchars($data['npm']); ?>)
                                                        
                                                        <small class="text-muted d-block mt-2">Keterlambatan Pengembalian:</small>
                                                        <strong><?= $selisih_telat; ?> Hari</strong>

                                                        <small class="text-muted d-block mt-2">Total Tagihan Denda Saat Ini:</small>
                                                        <h3 class="text-danger fw-bold mt-1">Rp <?= number_format($total_denda, 0, ',', '.'); ?></h3>
                                                    </div>
                                                    <p class="text-muted small">Pastikan mahasiswa telah menyerahkan uang denda sebesar nominal di atas sebelum Anda menekan tombol konfirmasi.</p>

                                                    <div class="d-flex gap-2 mt-4">
                                                        <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="bayar_denda" class="btn btn-success w-100 fw-semibold">
                                                            <i class="bi bi-check-circle me-1"></i> Konfirmasi Lunas
                                                        </button>
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