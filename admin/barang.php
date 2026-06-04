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
    $nama_file = 'DataBarang_' . date('d-m-Y_H.i.s') . '.xls';
    
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$nama_file\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    $query_export = mysqli_query($conn, "SELECT barang.*, kategori.nama_kategori FROM barang JOIN kategori ON kategori.id_kategori = barang.id_kategori ORDER BY barang.kode_barang ASC");
    ?>
    <meta charset="UTF-8">
    <table border="1">
        <thead>
            <tr style="background-color: #198754; color: #ffffff; font-weight: bold; height: 30px;">
                <th style="padding: 5px 15px;">Kode Barang</th>
                <th style="padding: 5px 15px;">Nama Barang</th>
                <th style="padding: 5px 15px;">Kategori</th>
                <th style="padding: 5px 15px;">Tahun</th>
                <th style="padding: 5px 15px;">Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($query_export)): ?>
                <tr style="height: 25px;">
                    <td style="padding: 5px 10px;"><?= htmlspecialchars($row['kode_barang']); ?></td>
                    <td style="padding: 5px 10px;"><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td style="padding: 5px 10px;"><?= htmlspecialchars($row['nama_kategori']); ?></td>
                    <td style="padding: 5px 10px; text-align: center;"><?= htmlspecialchars($row['tahun']); ?></td>
                    <td style="padding: 5px 10px; text-align: center;"><?= htmlspecialchars($row['stok']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
    exit;
}

// ==========================================
// LOGIC TAMBAH BARANG
// ==========================================
if (isset($_POST['tambah'])) {
    $nama         = htmlspecialchars($_POST['nama']);
    $id_kategori  = htmlspecialchars($_POST['id_kategori']);
    $tahun        = htmlspecialchars($_POST['tahun']);
    $stok         = htmlspecialchars($_POST['stok']);
    $gambar       = $_FILES['gambar']['name'];
    $tmpFile      = $_FILES['gambar']['tmp_name'];

    $folder = "img_barang/";

    if (!is_dir($folder)) {
        mkdir($folder, 0757, true);
    }

    move_uploaded_file($tmpFile, $folder . $gambar);

    function generateKodeBarang($conn)
    {
        $query = mysqli_query($conn, "SELECT MAX(id_barang) as last_id FROM barang");
        $data = mysqli_fetch_assoc($query);
        $lastId = $data['last_id'] + 1;
        $kode = "BRG-" . date("Ymd") . "-" . str_pad($lastId, 3, "0", STR_PAD_LEFT);
        return $kode;
    }

    $kode_barang = generateKodeBarang($conn);

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO barang (nama_barang, kode_barang, id_kategori, tahun, stok, gambar) VALUES (?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssisss", $nama, $kode_barang, $id_kategori, $tahun, $stok, $gambar);
    mysqli_stmt_execute($stmt);

    header("Location: barang.php");
    exit;
}

// ==========================================
// LOGIC EDIT BARANG
// ==========================================
if (isset($_POST['edit'])) {
    $id          = $_POST['id'];
    $nama        = htmlspecialchars($_POST['nama_barang']);
    $id_kategori = htmlspecialchars($_POST['id_kategori']);
    $tahun       = htmlspecialchars($_POST['tahun']);
    $stok        = htmlspecialchars($_POST['stok']);
    
    $gambar_baru = $_FILES['gambar']['name'];
    $tmpFile     = $_FILES['gambar']['tmp_name'];
    $folder      = "img_barang/";

    if (!empty($gambar_baru)) {
        move_uploaded_file($tmpFile, $folder . $gambar_baru);
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE barang SET nama_barang=?, id_kategori=?, tahun=?, stok=?, gambar=? WHERE id_barang=?"
        );
        mysqli_stmt_bind_param($stmt, "siiisi", $nama, $id_kategori, $tahun, $stok, $gambar_baru, $id);
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE barang SET nama_barang=?, id_kategori=?, tahun=?, stok=? WHERE id_barang=?"
        );
        mysqli_stmt_bind_param($stmt, "siiii", $nama, $id_kategori, $tahun, $stok, $id);
    }
    
    mysqli_stmt_execute($stmt);
    header("Location: barang.php");
    exit;
}

// ==========================================
// LOGIC HAPUS BARANG
// ==========================================
if (isset($_POST['hapus'])) {
    $id = $_POST['id'];
    
    $query_gambar = mysqli_query($conn, "SELECT gambar FROM barang WHERE id_barang='$id'");
    $data_gambar = mysqli_fetch_assoc($query_gambar);
    if (!empty($data_gambar['gambar']) && file_exists("img_barang/" . $data_gambar['gambar'])) {
        unlink("img_barang/" . $data_gambar['gambar']);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM barang WHERE id_barang=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    
    header("Location: barang.php");
    exit;
}

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
</head>

<body>

    <div class="admin-layout">

        <?php include 'assets/navbar.php' ?>

        <main class="content-admin">
            <div class="topbar-admin">
                <div>
                    <h1>Data Barang</h1>
                </div>
                <div class="top-buttons">
                    <a href="?action=export_excel" class="btn btn-export text-decoration-none d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                    <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        + Tambah Data
                    </button>
                </div>
            </div>

            <div>
                <div class="table-card">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Tahun</th>
                                <th>Stok</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $modal_barang_store = [];
                            $query = mysqli_query($conn, "SELECT barang.*, kategori.nama_kategori FROM barang JOIN kategori ON kategori.id_kategori = barang.id_kategori");
                            
                            while ($data = mysqli_fetch_assoc($query)) {
                                $modal_barang_store[] = $data;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($data['kode_barang']); ?></td>
                                    <td><?= htmlspecialchars($data['nama_barang']); ?></td>
                                    <td><?= htmlspecialchars($data['nama_kategori']); ?></td>
                                    <td><?= htmlspecialchars($data['tahun']); ?></td>
                                    <td><?= htmlspecialchars($data['stok']); ?></td>
                                    <td class="d-flex gap-2">
                                        <button class="btn-success-action" data-bs-toggle="modal" data-bs-target="#view<?= $data['id_barang']; ?>">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <button class="btn-success-action" data-bs-toggle="modal" data-bs-target="#edit<?= $data['id_barang']; ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn-danger-action" data-bs-toggle="modal" data-bs-target="#hapus<?= $data['id_barang']; ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h4 class="modal-title fw-bold text-success-custom">Tambah Barang</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Barang</label>
                            <input type="text" name="nama" class="form-control rounded-3" placeholder="Masukkan nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="id_kategori" class="form-select rounded-3" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                $kategori = mysqli_query($conn, "SELECT * FROM kategori");
                                while ($k = mysqli_fetch_assoc($kategori)) {
                                ?>
                                    <option value="<?= $k['id_kategori']; ?>"><?= $k['nama_kategori']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tahun</label>
                            <input type="number" name="tahun" class="form-control rounded-3" placeholder="Masukkan Tahun" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Stok</label>
                            <input type="number" name="stok" class="form-control rounded-3" placeholder="Masukkan Stok" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Gambar</label>
                            <input type="file" name="gambar" class="form-control rounded-3" accept="image/*" required>
                        </div>
                        <button type="submit" name="tambah" class="btn-add w-100 mt-2">Simpan Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    foreach ($modal_barang_store as $data) {
        $gambar_path = "img_barang/" . $data['gambar'];
    ?>
        
        <div class="modal fade" id="view<?= $data['id_barang']; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Detail Info Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-4 bg-light rounded-3 p-3 border d-flex align-items-center justify-content-center" style="height: 220px;">
                            <?php if (!empty($data['gambar']) && file_exists($gambar_path)): ?>
                                <img src="<?= $gambar_path; ?>" alt="Gambar Barang" class="img-fluid rounded-3 object-fit-contain" style="max-height: 100%; max-width: 100%;">
                            <?php else: ?>
                                <div class="text-muted small">
                                    <i class="bi bi-image fs-1 d-block mb-1"></i> Tidak ada gambar
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <table class="table table-borderless text-start m-0">
                            <tr>
                                <td class="fw-bold text-muted small" style="width: 35%;">Kode Barang</td>
                                <td class="fw-bold text-success">: <?= htmlspecialchars($data['kode_barang']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small">Nama Barang</td>
                                <td>: <?= htmlspecialchars($data['nama_barang']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small">Kategori</td>
                                <td>: <?= htmlspecialchars($data['nama_kategori']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small">Tahun Rilis</td>
                                <td>: <?= htmlspecialchars($data['tahun']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small">Jumlah Stok</td>
                                <td>: <span class="badge bg-secondary-subtle text-dark border px-2.5"><?= htmlspecialchars($data['stok']); ?> pcs</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light w-100 rounded-3" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="edit<?= $data['id_barang']; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Data Barang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" name="id" value="<?= $data['id_barang']; ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($data['nama_barang']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Kategori</label>
                                <select name="id_kategori" class="form-select" required>
                                    <?php
                                    $kategori_edit = mysqli_query($conn, "SELECT * FROM kategori");
                                    while ($ke = mysqli_fetch_assoc($kategori_edit)) {
                                        $selected = ($ke['id_kategori'] == $data['id_kategori']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $ke['id_kategori']; ?>" <?= $selected; ?>><?= $ke['nama_kategori']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Tahun</label>
                                <input type="number" name="tahun" class="form-control" value="<?= htmlspecialchars($data['tahun']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Stok</label>
                                <input type="number" name="stok" class="form-control" value="<?= htmlspecialchars($data['stok']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Ganti Gambar (Opsional)</label>
                                <?php if (!empty($data['gambar']) && file_exists($gambar_path)): ?>
                                    <div class="mb-2">
                                        <img src="<?= $gambar_path; ?>" class="img-thumbnail" style="height: 60px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="gambar" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="edit" class="btn btn-success">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="hapus<?= $data['id_barang']; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <form method="POST">
                        <div class="modal-body text-center p-4">
                            <input type="hidden" name="id" value="<?= $data['id_barang']; ?>">
                            <i class="bi bi-trash-fill text-danger fs-1"></i>
                            <h4 class="mt-3 fw-bold">Hapus Barang?</h4>
                            <p class="text-muted">Data barang <strong><?= htmlspecialchars($data['nama_barang']); ?></strong> akan dihapus permanen.</p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>