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

if (isset($_POST['tambah'])) {
    $id_user = $_SESSION['id_user'];
    $id_barang = htmlspecialchars($_POST['id_barang']);
    $jumlah_pinjam = intval($_POST['jumlah_pinjam']);
    $tgl_awal = htmlspecialchars($_POST['awal']);
    $tgl_akhir = htmlspecialchars($_POST['akhir']);
    $keperluan = htmlspecialchars($_POST['keperluan']);

    mysqli_begin_transaction($conn);

    try {
        $query_stok = mysqli_prepare($conn, "SELECT stok FROM barang WHERE id_barang = ? FOR UPDATE");
        mysqli_stmt_bind_param($query_stok, "s", $id_barang);
        mysqli_stmt_execute($query_stok);
        $res_stok = mysqli_stmt_get_result($query_stok);
        $barang = mysqli_fetch_assoc($res_stok);

        if (!$barang || $barang['stok'] < $jumlah_pinjam) {
            throw new Exception("Stok tidak mencukupi atau barang tidak ditemukan!");
        }

        $tanggal_sekarang = date('Ymd');
        $karakter_acak = strtoupper(substr(md5(time() . rand()), 0, 4));
        $kode_peminjaman = "UPNVJT-" . $tanggal_sekarang . "-" . $karakter_acak;

        $query_insert = mysqli_prepare(
            $conn,
            "INSERT INTO peminjaman (kode_peminjaman, id_user, id_barang, jumlah_pinjam, tgl_mulai, tgl_selesai, catatan) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param($query_insert, "sssisss", $kode_peminjaman, $id_user, $id_barang, $jumlah_pinjam, $tgl_awal, $tgl_akhir, $keperluan);

        if (!mysqli_stmt_execute($query_insert)) {
            throw new Exception(mysqli_stmt_error($query_insert));
        }

        mysqli_commit($conn);

        header("Location: index.php?pesan=berhasil_booking");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<h3>Proses Gagal!</h3>";
        echo "Pesan Error Program: " . $e->getMessage() . "<br>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPNVJT Music - Katalog Instrumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid pt-5 mt-4">
        <div class="row">
            <?php include 'assets/navbar.php' ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4 bg-content min-vh-100">
                <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil_booking'): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-3 small" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Berhasil!</strong> Pesanan peminjaman instrumen musik Anda telah disimpan dan berstatus Pending.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row align-items-center mb-4 g-3">
                    <div class="col-12 col-md-4">
                        <h1 class="h2 fw-bold text-success-custom mb-1">Katalog Barang</h1>
                        <p class="text-muted small m-0">Pilih instrumen berkualitas untuk kegiatan akademik Anda.</p>
                    </div>

                    <div class="col-12 col-sm-7 col-md-5">
                        <form action="" method="GET" id="formPencarian" class="position-relative">
                            <?php if (!empty($_GET['kategori'])): ?>
                                <input type="hidden" name="kategori" value="<?= htmlspecialchars($_GET['kategori']) ?>">
                            <?php endif; ?>
                            <?php if (!empty($_GET['sort'])): ?>
                                <input type="hidden" name="sort" value="<?= htmlspecialchars($_GET['sort']) ?>">
                            <?php endif; ?>

                            <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                                <span class="input-group-text bg-white border-0 pe-1 text-muted">
                                    <i class="bi bi-search ps-2"></i>
                                </span>
                                <input type="text" id="inputKeyword" name="keyword" value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>" class="form-control border-0 py-2.5 small bg-white text-dark focus-none" placeholder="Cari nama instrumen musik...">

                                <?php if (isset($_GET['keyword']) && $_GET['keyword'] !== ''): ?>
                                    <a href="index.php?<?= http_build_query(array_diff_key($_GET, ['keyword' => ''])) ?>" class="btn bg-white border-0 text-muted d-flex align-items-center">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-success-custom px-4 fw-semibold" type="submit">Cari</button>
                            </div>
                        </form>
                    </div>

                    <div class="col-12 col-sm-5 col-md-3 d-flex justify-content-sm-end gap-2">
                        <button class="btn btn-outline-secondary btn-custom-action w-100 w-sm-auto d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#modalFilterKategori">
                            <i class="bi bi-sliders"></i> Filter
                            <?= isset($_GET['kategori']) && $_GET['kategori'] != '' ? '<span class="badge bg-success">1</span>' : '' ?>
                        </button>

                        <div class="dropdown w-100 w-sm-auto">
                            <button class="btn btn-outline-secondary btn-custom-action w-100 d-flex align-items-center justify-content-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-arrow-down-up"></i> Urutkan
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                <li><a class="dropdown-menu-item dropdown-item small py-2" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'nama_asc'])) ?>">Nama Barang (A-Z)</a></li>
                                <li><a class="dropdown-menu-item dropdown-item small py-2" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'nama_desc'])) ?>">Nama Barang (Z-A)</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-menu-item dropdown-item small py-2" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'stok_desc'])) ?>">Stok Terbanyak</a></li>
                                <li><a class="dropdown-menu-item dropdown-item small py-2" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'stok_asc'])) ?>">Stok Tersedikit</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-menu-item dropdown-item small py-2" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'tahun_desc'])) ?>">Tahun Terbaru</a></li>
                                <li><a class="dropdown-menu-item dropdown-item small py-2" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'tahun_asc'])) ?>">Tahun Terlama</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <?php if (isset($_GET['kategori']) || isset($_GET['keyword']) || isset($_GET['sort'])): ?>
                    <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                        <span class="text-muted small">Filter Aktif:</span>
                        <?php if (!empty($_GET['keyword'])): ?>
                            <span class="badge bg-light text-dark border p-2 rounded-3">Cari: "<?= htmlspecialchars($_GET['keyword']) ?>"</span>
                        <?php endif; ?>
                        <?php if (!empty($_GET['kategori'])):
                            $stmt_kat = mysqli_prepare($conn, "SELECT nama_kategori FROM kategori WHERE id_kategori = ?");
                            mysqli_stmt_bind_param($stmt_kat, "i", $_GET['kategori']);
                            mysqli_stmt_execute($stmt_kat);
                            $res_kat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_kat));
                        ?>
                            <span class="badge bg-light text-dark border p-2 rounded-3">Kategori: <?= htmlspecialchars($res_kat['nama_kategori'] ?? '') ?></span>
                        <?php endif; ?>
                        <a href="index.php" class="btn btn-sm btn-link text-danger p-0 ms-2 text-decoration-none small">Reset Semua</a>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-5">
                    <?php
                    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
                    $kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
                    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';

                    $sql = "SELECT barang.*, kategori.nama_kategori FROM barang 
                            INNER JOIN kategori ON kategori.id_kategori = barang.id_kategori WHERE 1=1";

                    $params = [];
                    $types = "";

                    if ($keyword !== '') {
                        $sql .= " AND barang.nama_barang LIKE ?";
                        $params[] = "%" . $keyword . "%";
                        $types .= "s";
                    }

                    if ($kategori !== '') {
                        $sql .= " AND barang.id_kategori = ?";
                        $params[] = $kategori;
                        $types .= "i";
                    }

                    switch ($sort) {
                        case 'nama_asc':
                            $sql .= " ORDER BY barang.nama_barang ASC";
                            break;
                        case 'nama_desc':
                            $sql .= " ORDER BY barang.nama_barang DESC";
                            break;
                        case 'stok_asc':
                            $sql .= " ORDER BY barang.stok ASC";
                            break;
                        case 'stok_desc':
                            $sql .= " ORDER BY barang.stok DESC";
                            break;
                        case 'tahun_asc':
                            $sql .= " ORDER BY barang.tahun ASC";
                            break;
                        case 'tahun_desc':
                            $sql .= " ORDER BY barang.tahun DESC";
                            break;
                        default:
                            $sql .= " ORDER BY barang.id_barang DESC";
                            break;
                    }

                    $stmt = mysqli_prepare($conn, $sql);
                    if (!empty($params)) {
                        mysqli_stmt_bind_param($stmt, $types, ...$params);
                    }
                    mysqli_stmt_execute($stmt);
                    $query = mysqli_stmt_get_result($stmt);

                    if (mysqli_num_rows($query) > 0) {
                        while ($data = mysqli_fetch_assoc($query)) {
                    ?>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card instrument-card h-100 border-0 rounded-4 shadow-sm overflow-hidden">
                                    <div class="position-relative ratio ratio-4x3 bg-light">
                                        <img src="admin/img_barang/<?= $data['gambar']; ?>" class="img-fluid object-fit-cover" alt="<?= $data['nama_barang']; ?>">
                                    </div>
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="mb-4">
                                            <span class="text-muted small text-uppercase fw-semibold d-block mb-1"><?= $data['nama_kategori']; ?></span>
                                            <h5 class="card-title fw-bold text-dark mb-3"><?= $data['nama_barang']; ?>, <?= $data['tahun']; ?></h5>
                                            <div class="badge-container position-absolute top-0 start-0 p-3 d-flex gap-2">
                                                <?php if ($data['stok'] > 0): ?>
                                                    <span class="badge bg-success rounded-pill px-3 py-1" style="box-shadow: 0px 0px 15px 0px rgba(0, 0, 0, 0.3);">Tersedia</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger rounded-pill px-3 py-1" style="box-shadow: 0px 0px 15px 0px rgba(0, 0, 0, 0.3);">Habis</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-muted small m-0"><i class="bi bi-box-seam me-1"></i>Stok: <?= $data['stok']; ?></p>
                                        </div>
                                        <div class="d-flex gap-2 mt-auto">
                                            <?php if ($data['stok'] > 0): ?>
                                                <button class="btn btn-success-custom w-100 rounded-3 py-2 fw-semibold btn-pinjam-modal"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalPinjamInstrumen"
                                                    data-id="<?= $data['id_barang']; ?>"
                                                    data-nama="<?= htmlspecialchars($data['nama_barang']); ?>"
                                                    data-tahun="<?= htmlspecialchars($data['tahun']); ?>"
                                                    data-kategori="<?= htmlspecialchars($data['nama_kategori']); ?>"
                                                    data-gambar="admin/img_barang/<?= $data['gambar']; ?>"
                                                    data-stok="<?= $data['stok']; ?>">
                                                    Pinjam
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary w-100 rounded-3 py-2 fw-semibold" disabled>
                                                    Stok Habis
                                                </button>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                    } else {
                        ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-search display-2 text-muted"></i>
                            <h4 class="mt-3 fw-bold text-dark">Instrumen Tidak Ditemukan</h4>
                            <p class="text-muted">Tidak ada instrumen musik yang sesuai kriteria pencarian atau filter Anda.</p>
                            <a href="index.php" class="btn btn-success-custom px-4 py-2 rounded-pill fw-semibold mt-2">Lihat Semua Kategori</a>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalFilterKategori" tabindex="-1" aria-labelledby="modalFilterLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header border-0 px-4 py-3 ">
                    <h5 class="modal-title fw-bold text-success-custom" id="modalFilterLabel"><i class="bi bi-sliders me-2"></i>Pilih Kategori</h5>
                    <button type="button" class="btn-close text-success-custom" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="" method="GET" class="d-flex flex-column gap-3">
                        <?php if (!empty($keyword)): ?>
                            <input type="hidden" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
                        <?php endif; ?>
                        <?php if (!empty($sort)): ?>
                            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                        <?php endif; ?>

                        <div class="form-check p-3 border rounded-3 bg-light-subtle">
                            <input class="form-check-input ms-0 me-3" type="radio" name="kategori" id="kat_semua" value="" <?= $kategori === '' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold text-dark" for="kat_semua">Semua Kategori</label>
                        </div>

                        <?php
                        $q_modal_kat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
                        while ($row_kat = mysqli_fetch_assoc($q_modal_kat)):
                        ?>
                            <div class="form-check p-3 border rounded-3">
                                <input class="form-check-input ms-0 me-3" type="radio" name="kategori" id="kat_<?= $row_kat['id_kategori'] ?>" value="<?= $row_kat['id_kategori'] ?>" <?= $kategori == $row_kat['id_kategori'] ? 'checked' : '' ?>>
                                <label class="form-check-label text-dark" for="kat_<?= $row_kat['id_kategori'] ?>">
                                    <?= htmlspecialchars($row_kat['nama_kategori']) ?>
                                </label>
                            </div>
                        <?php endwhile; ?>

                        <div class="mt-3 d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary w-50 py-2.5 rounded-pill fw-semibold" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success-custom w-50 py-2.5 rounded-pill fw-bold">Terapkan Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPinjamInstrumen" tabindex="-1" aria-labelledby="modalPinjamLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 450px;">
            <div class="modal-content border-0 rounded-4 overflow-hidden" style="background-color: var(--bg-content);">
                <div class="modal-header border-0 px-4 py-3 bg-lightblue">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn border-0 p-0 m-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-arrow-left fs-4 text-success-custom"></i>
                        </button>
                        <h5 class="modal-title fw-bold m-0 fs-5 text-success-custom" id="modalPinjamLabel">Pinjam Instrumen</h5>
                    </div>
                </div>

                <div class="modal-body p-4">
                    <?php
                    $id_user_session = $_SESSION['id_user'];
                    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id_user = ? LIMIT 1");
                    mysqli_stmt_bind_param($stmt, "s", $id_user_session);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $user_data = mysqli_fetch_assoc($result);
                    ?>

                    <form action="" method="POST" class="d-flex flex-column gap-4">
                        <input type="hidden" name="id_mahasiswa" value="<?= htmlspecialchars($id_user_session); ?>">
                        <input type="hidden" name="id_barang" id="modal-input-id">

                        <div class="card border-light-subtle rounded-4 p-3 shadow-sm bg-white">
                            <div class="d-flex gap-3 align-items-center mb-2">
                                <img id="modal-img" src="" alt="Gambar Instrumen" class="rounded-3 border object-fit-cover" style="width: 75px; height: 75px;">
                                <div>
                                    <h6 id="modal-nama-barang" class="fw-bold text-dark m-0 fs-5">Nama Barang</h6>
                                    <p id="modal-kategori-barang" class="text-muted small m-0 mb-2">Kategori</p>
                                    <span class="badge rounded-pill text-success border border-success-subtle px-2.5 py-1 bg-light" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">
                                        <i class="bi bi-check-circle-fill me-1"></i> TERSEDIA
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-bold text-dark mb-2" style="font-size: 0.85rem;">Identitas Mahasiswa</label>
                            <div class="card border-0 rounded-4 p-3 d-flex flex-row align-items-center gap-3 bg-lightblue">
                                <div class="profile-avatar text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 46px; height: 46px; font-size: 0.9rem; background-color: var(--bg-success-custom, #0A5C2C); border-radius: 50%;">
                                    <?= isset($user_data['nama']) ? strtoupper(substr($user_data['nama'], 0, 2)) : '??'; ?>
                                </div>
                                <div>
                                    <h6 class="fw-bold m-0 text-success-custom" style="font-size: 1rem;">
                                        <?= htmlspecialchars($user_data['nama'] ?? 'Nama Tidak Ditemukan'); ?>
                                    </h6>
                                    <p class="text-muted small m-0" style="font-size: 0.8rem;">
                                        NIM: <?= htmlspecialchars($user_data['npm'] ?? '-'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-bold text-dark mb-2" style="font-size: 0.85rem;">Jumlah Pinjam</label>
                            <input type="number" id="jumlah" name="jumlah_pinjam" min="1" required class="form-control py-2.5 px-3 rounded-3 bg-white border-light-subtle text-muted shadow-sm" style="font-size: 0.88rem;" placeholder="Masukkan jumlah...">
                        </div>

                        <div>
                            <label class="form-label fw-bold text-dark mb-2" style="font-size: 0.85rem;">Tanggal Mulai Pinjam / Booking</label>
                            <input type="date" id="tgl_mulai" name="awal" required class="form-control py-2.5 px-3 rounded-3 bg-white border-light-subtle text-muted shadow-sm" style="font-size: 0.88rem;">
                        </div>

                        <div>
                            <label class="form-label fw-bold text-dark mb-2" style="font-size: 0.85rem;">Tanggal Pengembalian</label>
                            <input type="date" id="tgl_kembali" name="akhir" required class="form-control py-2.5 px-3 rounded-3 bg-white border-light-subtle text-muted shadow-sm" style="font-size: 0.88rem;">
                        </div>

                        <div>
                            <label class="form-label fw-bold text-dark mb-2" style="font-size: 0.85rem;">Keperluan Peminjaman</label>
                            <textarea id="keperluan" name="keperluan" rows="3" placeholder="Contoh: Latihan mandiri untuk tugas kelompok praktikum musik..." class="form-control py-2.5 px-3 rounded-3 bg-white border-light-subtle text-muted shadow-sm" style="resize: none; font-size: 0.85rem;"></textarea>
                        </div>

                        <div class="card border-0 rounded-4 p-3 position-relative overflow-hidden bg-lightblue">
                            <div class="position-absolute top-0 start-0 bottom-0" style="width: 6px; background-color: #B38E34;"></div>
                            <div class="form-check d-flex gap-2 ps-2 align-items-start">
                                <input class="form-check-input flex-shrink-0 mt-1 border-secondary-subtle" style="margin-left: -2px;"type="checkbox" value="setuju" id="sk_check" required style="width: 18px; height: 18px;">
                                <label class="form-check-label text-dark" for="sk_check" style="font-size: 0.78rem; line-height: 1.4;">
                                    <strong class="d-block mb-1 text-dark" style="font-size: 0.82rem;">Syarat & Ketentuan</strong>
                                    Saya bersedia menjaga kebersihan dan kelengkapan instrumen. Kerusakan atau kehilangan menjadi tanggung jawab penuh peminjaman.
                                </label>
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="submit" name="tambah" class="btn btn-success-custom w-100 py-2.5 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2 fs-6 shadow-sm">
                                <i class="bi bi-check2-square fs-5"></i> Konfirmasi Peminjaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validasi agar pencarian kosong tidak bisa di-submit via Enter maupun tombol cari
        const formPencarian = document.getElementById('formPencarian');
        const inputKeyword = document.getElementById('inputKeyword');

        if (formPencarian && inputKeyword) {
            formPencarian.addEventListener('submit', function(event) {
                // .trim() digunakan untuk menghapus spasi kosong yang tidak sengaja diketik
                if (inputKeyword.value.trim() === '') {
                    event.preventDefault(); // Mencegah form melakukan submit / enter
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const inputTglMulai = document.getElementById('tgl_mulai');
            const inputTglKembali = document.getElementById('tgl_kembali');

            const hariIni = new Date();
            const yyyy = hariIni.getFullYear();
            const mm = String(hariIni.getMonth() + 1).padStart(2, '0');
            const dd = String(hariIni.getDate()).padStart(2, '0');
            const formatHariIni = `${yyyy}-${mm}-${dd}`;

            inputTglMulai.min = formatHariIni;
            inputTglKembali.min = formatHariIni;

            inputTglMulai.addEventListener('change', function() {
                if (this.value) {
                    inputTglKembali.min = this.value;
                    if (inputTglKembali.value && inputTglKembali.value < this.value) {
                        inputTglKembali.value = this.value;
                    }
                }
            });

            const menuItems = document.querySelectorAll(".menu-data");
            const activeMenu = localStorage.getItem("activeMenu");

            if (activeMenu) {
                menuItems.forEach(item => {
                    item.classList.remove("active-category");
                    if (item.getAttribute("href") === activeMenu) {
                        item.classList.add("active-category");
                    }
                });
            }

            menuItems.forEach(item => {
                item.addEventListener("click", function() {
                    localStorage.setItem("activeMenu", this.getAttribute("href"));
                });
            });

            const tombolPinjam = document.querySelectorAll(".btn-pinjam-modal");
            tombolPinjam.forEach(btn => {
                btn.addEventListener("click", function() {
                    const idBarang = this.getAttribute("data-id");
                    const namaBarang = this.getAttribute("data-nama");
                    const kategoriBarang = this.getAttribute("data-kategori");
                    const gambarBarang = this.getAttribute("data-gambar");
                    const tahunBarang = this.getAttribute("data-tahun");
                    const stokBarang = this.getAttribute("data-stok");

                    document.getElementById("modal-input-id").value = idBarang;
                    document.getElementById("modal-nama-barang").textContent = `${namaBarang}, ${tahunBarang}`;
                    document.getElementById("modal-kategori-barang").textContent = kategoriBarang;
                    document.getElementById("modal-img").setAttribute("src", gambarBarang);

                    const inputJumlah = document.getElementById("jumlah");
                    inputJumlah.max = stokBarang;
                    inputJumlah.placeholder = `Maksimal ${stokBarang} pcs`;
                });
            });
        });
    </script>
</body>

</html>