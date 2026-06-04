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
    $nama_file = 'DataKategori_' . date('d-m-Y_H.i.s') . '.xls';

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$nama_file\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    $query_export = mysqli_query($conn, "SELECT nama_kategori FROM kategori ORDER BY nama_kategori ASC");
?>
    <meta charset="UTF-8">
    <table border="1">
        <thead>
            <tr style="background-color: #198754; color: #ffffff; font-weight: bold; height: 30px;">
                <th style="padding: 5px 20px;">Nama Kategori</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($query_export)): ?>
                <tr style="height: 25px;">
                    <td style="padding: 5px 15px;"><?= htmlspecialchars($row['nama_kategori']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php
    exit;
}

// HANDLER PROSES FORM (TAMBAH, EDIT, HAPUS)
if (isset($_POST['tambah'])) {
    $nama   = htmlspecialchars($_POST['nama']);
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO kategori (nama_kategori) VALUES (?)"
    );

    mysqli_stmt_bind_param($stmt, "s", $nama);
    mysqli_stmt_execute($stmt);

    header("Location: kategori.php");
    exit;
}

if (isset($_POST['edit'])) {
    $id     = $_POST['id'];
    $nama   = htmlspecialchars($_POST['nama']);
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE kategori SET nama_kategori=? WHERE id_kategori=?"
    );
    mysqli_stmt_bind_param($stmt, "si", $nama, $id);
    mysqli_stmt_execute($stmt);
    header("Location: kategori.php");
    exit;
}

if (isset($_POST['hapus'])) {
    $id = $_POST['id'];
    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM kategori WHERE id_kategori=?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: kategori.php");
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
                    <h1>Data Kategori</h1>
                </div>
                <div class="top-buttons">
                    <a href="?action=export_excel" class="btn btn-export text-decoration-none d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i>
                        Export Excel
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
                                <th>Nama Kategori</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $query = mysqli_query($conn, "SELECT * FROM kategori");
                            while ($data = mysqli_fetch_assoc($query)) {
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($data['nama_kategori']); ?></td>
                                    <td class="d-flex gap-2">
                                        <button class="btn-success-action" data-bs-toggle="modal" data-bs-target="#edit<?= $data['id_kategori']; ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn-danger-action" data-bs-toggle="modal" data-bs-target="#hapus<?= $data['id_kategori']; ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="edit<?= $data['id_kategori']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Kategori</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $data['id_kategori']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Nama Kategori</label>
                                                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama_kategori']); ?>" required>
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

                                <div class="modal fade" id="hapus<?= $data['id_kategori']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4">
                                            <form method="POST">
                                                <div class="modal-body text-center p-4">
                                                    <input type="hidden" name="id" value="<?= $data['id_kategori']; ?>">
                                                    <i class="bi bi-trash-fill text-danger fs-1"></i>
                                                    <h4 class="mt-3 fw-bold">Hapus Data?</h4>
                                                    <p class="text-muted">Data kategori ini akan dihapus permanen.</p>
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

    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h4 class="modal-title fw-bold text-success-custom">Tambah Kategori</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama Kategori</label>
                            <input type="text" name="nama" class="form-control rounded-3" placeholder="Masukkan Nama Kategori" required>
                        </div>
                        <button type="submit" name="tambah" class="btn-add w-100">Simpan Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>