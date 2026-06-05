<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

$id_user_session = $_SESSION['id_user'] ?? '';

if (isset($_POST['upload_foto']) && !empty($id_user_session)) {
    $nama_file   = $_FILES['foto_profil']['name'];
    $ukuran_file = $_FILES['foto_profil']['size'];
    $error       = $_FILES['foto_profil']['error'];
    $tmp_name    = $_FILES['foto_profil']['tmp_name'];

    if ($error === 0) {
        $ekstensi_valid = ['jpg', 'jpeg', 'png'];
        $ekstensi_file  = explode('.', $nama_file);
        $ekstensi_file  = strtolower(end($ekstensi_file));

        if (in_array($ekstensi_file, $ekstensi_valid)) {
            if ($ukuran_file <= 2097152) {

                $query_lama = mysqli_query($conn, "SELECT foto FROM users WHERE id_user = '$id_user_session' LIMIT 1");
                $data_lama  = mysqli_fetch_assoc($query_lama);
                if (!empty($data_lama['foto'])) {
                    $path_foto_lama = 'admin/img_users/' . $data_lama['foto'];
                    if (file_exists($path_foto_lama)) {
                        unlink($path_foto_lama); 
                    }
                }

                $nama_file_baru = uniqid() . '.' . $ekstensi_file;
                $target_direktori = 'admin/img_users/' . $nama_file_baru;

                if (move_uploaded_file($tmp_name, $target_direktori)) {
                    $update_query = mysqli_query($conn, "UPDATE users SET foto = '$nama_file_baru' WHERE id_user = '$id_user_session'");

                    if ($update_query) {
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit;
                    }
                }
            }
        }
    }
}

if (!empty($id_user_session)) {
    $query = mysqli_query($conn, "SELECT foto, nama, email FROM users WHERE id_user = '$id_user_session' LIMIT 1");
    if ($row = mysqli_fetch_assoc($query)) {
        $gambar_mhs = $row['foto'];
        $email = $row['email'];
        $nama = $row['nama'];
    }
}

$inisial = '';
$kata = explode(' ', trim($nama));
if (count($kata) >= 2) {
    $inisial = strtoupper(substr($kata[0], 0, 1) . substr($kata[1], 0, 1));
} else {
    $inisial = strtoupper(substr($nama, 0, 2));
}

$path_foto = 'admin/img_users/' . $gambar_mhs;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top px-4 py-3">
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center">
            <button class="btn btn-link link-dark p-0 me-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                <i class="bi bi-list fs-3"></i>
            </button>
            <a class="navbar-brand fw-bold text-success-custom fs-4 m-0" href="#">UPNVJT Music</a>
        </div>

        <div class="d-flex align-items-center gap-4">

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle no-caret" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if (!empty($gambar_mhs) && file_exists($path_foto)): ?>
                        <img src="<?= $path_foto; ?>" alt="Profil" class="profile-avatar object-fit-cover border shadow-sm" style="width: 40px; height: 40px; border-radius: 50%; cursor: pointer;">
                    <?php else: ?>
                        <div class="profile-avatar d-flex align-items-center justify-content-center text-white fw-bold bg-success" style="width: 40px; height: 40px; border-radius: 50%; cursor: pointer;">
                            <?= $inisial; ?>
                        </div>
                    <?php endif; ?>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 py-2" aria-labelledby="dropdownUser" style="min-width: 240px;">
                    <li>
                        <div class="dropdown-header d-flex flex-column px-3 py-2">
                            <span class="fw-bold text-dark fs-6 text-truncate" style="max-width: 200px;"><?= htmlspecialchars($nama); ?></span>
                            <span class="text-muted small text-truncate" style="max-width: 200px;"><?= htmlspecialchars($email); ?></span>
                        </div>
                    </li>
                    <li>
                        <hr class="dropdown-divider my-2">
                    </li>

                    <li>
                        <a class="dropdown-item small py-2.5 d-flex align-items-center gap-2 text-dark" href="#" data-bs-toggle="modal" data-bs-target="#modalGantiFoto">
                            <i class="bi bi-camera text-muted fs-5"></i> Ganti Foto Profil
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item small py-2.5 d-flex align-items-center gap-2 text-danger fw-semibold" href="logout.php">
                            <i class="bi bi-box-arrow-right fs-5"></i> Keluar / Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-sidebar sidebar collapse border-end px-3 pt-4">
    <div class="sidebar-sticky">
        <p class="text-muted small fw-bold text-uppercase tracking-wider mb-2">Kategori</p>
        <ul class="nav flex-column gap-1 mb-4">

            <li class="nav-item">
                <a class="nav-link menu-item <?= ($current_page == 'index.php' || $current_page == '') ? 'active-category text-success fw-bold' : 'text-dark'; ?>" href="index.php">
                    <i class="bi bi-grid-fill me-2"></i> Semua Alat
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-item <?= ($current_page == 'aturan_pinjam.php') ? 'active-category text-success fw-bold' : 'text-dark'; ?>" href="aturan_pinjam.php">
                    <i class="bi bi-info-circle me-2"></i> Aturan Pinjam
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-item <?= ($current_page == 'history.php') ? 'active-category text-success fw-bold' : 'text-dark'; ?>" href="history.php">
                    <i class="bi bi-clock-history me-2"></i> History
                </a>
            </li>

        </ul>
        <hr class="text-muted my-4">

        <p class="text-muted small fw-bold text-uppercase tracking-wider mb-2">Status Akun</p>
        <div class="card card-status border-0 rounded-4 p-3 mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="status-dot"></span>
                <span class="fw-bold text-success small">Active</span>
            </div>
        </div>
    </div>
</nav>

<div class="modal fade" id="modalGantiFoto" tabindex="-1" aria-labelledby="modalGantiFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="modalGantiFotoLabel">Perbarui Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4 text-center">
                    <div class="mb-4 d-inline-block position-relative">
                        <?php if (!empty($gambar_mhs) && file_exists($path_foto)): ?>
                            <img src="<?= $path_foto; ?>" class="border object-fit-cover shadow-sm" style="width: 100px; height: 100px; border-radius: 50%;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center text-white fw-bold bg-success display-6 shadow-sm" style="width: 100px; height: 100px; border-radius: 50%;">
                                <?= $inisial; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="text-start mb-3">
                        <label for="inputFoto" class="form-label small fw-bold text-muted">Pilih File Foto Baru</label>
                        <input class="form-control form-control-sm rounded-3 text-muted" type="file" id="inputFoto" name="foto_profil" accept="image/png, image/jpeg, image/jpg" required>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Format: JPG, JPEG, atau PNG. Maksimal ukuran 2MB.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex gap-2">
                    <button type="submit" name="upload_foto" class="btn btn-success-custom w-50 py-2 rounded-pill fw-bold small">Simpan Foto</button>
                </div>
            </form>
        </div>
    </div>
</div>