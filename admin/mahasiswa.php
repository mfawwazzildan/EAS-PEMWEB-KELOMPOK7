<?php

include '../koneksi.php';
session_start();


$target_dir = "../admin/img_users/";

if (isset($_GET['action']) && $_GET['action'] === 'export_excel') {
    $nama_file = 'DataMahasiswa_' . date('d-m-Y_H.i.s') . '.xls';

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$nama_file\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    $query_export = mysqli_query($conn, "SELECT nama, npm, email, telp FROM users ORDER BY nama ASC");
?>
    <meta charset="UTF-8">
    <table border="1">
        <thead>
            <tr style="background-color: #198754; color: #ffffff; font-weight: bold; height: 30px;">
                <th style="padding: 5px 10px;">Nama</th>
                <th style="padding: 5px 10px;">NPM</th>
                <th style="padding: 5px 10px;">Email</th>
                <th style="padding: 5px 10px;">Nomor Telepon</th>
                <th style="padding: 5px 10px;">Level</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($query_export)): ?>
                <tr style="height: 25px;">
                    <td style="padding: 5px;"><?= htmlspecialchars($row['nama']); ?></td>
                    <td style="mso-number-format:'\@'; padding: 5px;"><?= htmlspecialchars($row['npm']); ?></td>
                    <td style="padding: 5px;"><?= htmlspecialchars($row['email']); ?></td>
                    <td style="mso-number-format:'\@'; padding: 5px;"><?= htmlspecialchars($row['telp']); ?></td>
                    <td style="padding: 5px;"><?= htmlspecialchars($row['level']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php
    exit;
}

if (isset($_POST['tambah'])) {
    $nama   = htmlspecialchars($_POST['nama']);
    $npm    = htmlspecialchars($_POST['npm']);
    $email  = htmlspecialchars($_POST['email']);
    $telp   = htmlspecialchars($_POST['telp']);
    $pw     = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $level = htmlspecialchars($_POST['level']);

    $nama_foto_baru = "";
    if (!empty($_FILES['foto']['name'])) {
        $nama_file = $_FILES['foto']['name'];
        $tmp_name  = $_FILES['foto']['tmp_name'];
        $ekstensi  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        $nama_foto_baru = uniqid() . '.' . $ekstensi;
        move_uploaded_file($tmp_name, $target_dir . $nama_foto_baru);
    }

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (nama, npm, email, telp, password, level, foto) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "sssssss", $nama, $npm, $email, $telp, $pw, $level, $nama_foto_baru);
    mysqli_stmt_execute($stmt);

    header("Location: mahasiswa.php");
    exit;
}

if (isset($_POST['edit'])) {
    $id     = $_POST['id'];
    $nama   = htmlspecialchars($_POST['nama']);
    $npm    = htmlspecialchars($_POST['npm']);
    $email  = htmlspecialchars($_POST['email']);
    $telp   = htmlspecialchars($_POST['telp']);
    $password_baru = $_POST['password'];

    $ubah_password = !empty($password_baru);
    if ($ubah_password) {
        $pw_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
    }

    if (!empty($_FILES['foto']['name'])) {
        $nama_file = $_FILES['foto']['name'];
        $tmp_name  = $_FILES['foto']['tmp_name'];
        $ekstensi  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        $nama_foto_baru = uniqid() . '.' . $ekstensi;

        if (move_uploaded_file($tmp_name, $target_dir . $nama_foto_baru)) {
            $query_lama = mysqli_query($conn, "SELECT foto FROM users WHERE id_user = '$id' LIMIT 1");
            $data_lama  = mysqli_fetch_assoc($query_lama);
            if (!empty($data_lama['foto']) && file_exists($target_dir . $data_lama['foto'])) {
                unlink($target_dir . $data_lama['foto']);
            }

            if ($ubah_password) {
                $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, npm=?, email=?, telp=?, foto=?, password=? WHERE id_user=?");
                mysqli_stmt_bind_param($stmt, "ssssssi", $nama, $npm, $email, $telp, $nama_foto_baru, $pw_hashed, $id);
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, npm=?, email=?, telp=?, foto=? WHERE id_user=?");
                mysqli_stmt_bind_param($stmt, "sssssi", $nama, $npm, $email, $telp, $nama_foto_baru, $id);
            }
        }
    } else {
        if ($ubah_password) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, npm=?, email=?, telp=?, password=? WHERE id_user=?");
            mysqli_stmt_bind_param($stmt, "sssssi", $nama, $npm, $email, $telp, $pw_hashed, $id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, npm=?, email=?, telp=? WHERE id_user=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $nama, $npm, $email, $telp, $id);
        }
    }

    mysqli_stmt_execute($stmt);
    header("Location: mahasiswa.php");
    exit;
}

if (isset($_POST['hapus'])) {
    $id = $_POST['id'];

    $query_lama = mysqli_query($conn, "SELECT foto FROM users WHERE id_user = '$id' LIMIT 1");
    $data_lama  = mysqli_fetch_assoc($query_lama);
    if (!empty($data_lama['foto']) && file_exists($target_dir . $data_lama['foto'])) {
        unlink($target_dir . $data_lama['foto']);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id_user=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: mahasiswa.php");
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
                    <h1>Data Users</h1>
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
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>NPM</th>
                                <th>Email</th>
                                <th>Nomer Telepon</th>
                                <th>Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $modal_data_store = [];

                            $query = mysqli_query($conn, "SELECT * FROM users");
                            while ($data = mysqli_fetch_assoc($query)) {
                                $modal_data_store[] = $data;

                                $avatar_path = "../admin/img_users/" . $data['foto'];
                                $kata = explode(' ', trim($data['nama']));
                                $inisial = (count($kata) >= 2) ? strtoupper(substr($kata[0], 0, 1) . substr($kata[1], 0, 1)) : strtoupper(substr($data['nama'], 0, 2));
                            ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($data['foto']) && file_exists($avatar_path)): ?>
                                            <img src="<?= $avatar_path; ?>" alt="User" class="object-fit-cover rounded-circle border" style="width: 40px; height: 40px;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center text-white fw-bold bg-success rounded-circle small" style="width: 40px; height: 40px;">
                                                <?= $inisial; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($data['nama']); ?></td>
                                    <td><?= htmlspecialchars($data['npm']); ?></td>
                                    <td><?= htmlspecialchars($data['email']); ?></td>
                                    <td><?= htmlspecialchars($data['telp']); ?></td>
                                    <td><?= htmlspecialchars($data['level']); ?></td>
                                    <td class="d-flex gap-2">
                                        <button class="btn-success-action" data-bs-toggle="modal" data-bs-target="#view<?= $data['id_user']; ?>">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <button class="btn-success-action" data-bs-toggle="modal" data-bs-target="#edit<?= $data['id_user']; ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn-danger-action" data-bs-toggle="modal" data-bs-target="#hapus<?= $data['id_user']; ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <?php
                foreach ($modal_data_store as $data) {
                    $avatar_path = "../admin/img_users/" . $data['foto'];
                    $kata = explode(' ', trim($data['nama']));
                    $inisial = (count($kata) >= 2) ? strtoupper(substr($kata[0], 0, 1) . substr($kata[1], 0, 1)) : strtoupper(substr($data['nama'], 0, 2));
                ?>
                    <div class="modal fade" id="view<?= $data['id_user']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold">Detail Data User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 text-center">
                                    <div class="mb-3">
                                        <?php if (!empty($data['foto']) && file_exists($avatar_path)): ?>
                                            <img src="<?= $avatar_path; ?>" class="object-fit-cover rounded-circle border shadow-sm" style="width: 100px; height: 100px;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center text-white fw-bold bg-success rounded-circle shadow-sm mx-auto display-6" style="width: 100px; height: 100px;">
                                                <?= $inisial; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <table class="table table-borderless text-start m-0">
                                        <tr>
                                            <td class="fw-bold text-muted small" style="width: 35%;">Nama Lengkap</td>
                                            <td>: <?= htmlspecialchars($data['nama']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted small">NPM</td>
                                            <td>: <?= htmlspecialchars($data['npm']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted small">Email</td>
                                            <td>: <?= htmlspecialchars($data['email']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted small">No. Telepon</td>
                                            <td>: <?= htmlspecialchars($data['telp']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted small">Level Hak Akses</td>
                                            <td>: <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><?= ucfirst($data['level']); ?></span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light w-100 rounded-3" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="edit<?= $data['id_user']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="id" value="<?= $data['id_user']; ?>">

                                        <div class="text-center mb-3">
                                            <?php if (!empty($data['foto']) && file_exists($avatar_path)): ?>
                                                <img src="<?= $avatar_path; ?>" class="object-fit-cover rounded-circle border mb-2" style="width: 70px; height: 70px;">
                                            <?php endif; ?>
                                            <div class="text-start">
                                                <label class="form-label small fw-bold">Ganti Foto Profil (Opsional)</label>
                                                <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Nama</label>
                                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">NPM</label>
                                            <input type="text" name="npm" class="form-control" value="<?= htmlspecialchars($data['npm']); ?>" >
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">No Telepon</label>
                                            <input type="text" name="telp" class="form-control" value="<?= htmlspecialchars($data['telp']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Password Baru</label>
                                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin merubah password">
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

                    <div class="modal fade" id="hapus<?= $data['id_user']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">
                                <form method="POST">
                                    <div class="modal-body text-center p-4">
                                        <input type="hidden" name="id" value="<?= $data['id_user']; ?>">
                                        <i class="bi bi-trash-fill text-danger fs-1"></i>
                                        <h4 class="mt-3 fw-bold">Hapus Data?</h4>
                                        <p class="text-muted">Data User akan dihapus permanen.</p>
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
            </div>
        </main>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h4 class="modal-title fw-bold text-success-custom">Tambah User</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Foto Profil (Opsional)</label>
                            <input type="file" name="foto" class="form-control rounded-3" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama</label>
                            <input type="text" name="nama" class="form-control rounded-3" placeholder="Masukkan nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">NPM</label>
                            <input type="text" name="npm" class="form-control rounded-3" placeholder="Masukkan NPM">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="Masukkan email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nomor Telepon</label>
                            <input type="text" name="telp" class="form-control rounded-3" placeholder="Masukkan nomor telepon" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Password</label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Masukkan password" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Level</label>
                            <select name="level" class="form-select rounded-3" required>
                                <option value="Mahasiswa" selected>Mahasiswa</option>
                                <option value="Admin">Admin</option>
                            </select>
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