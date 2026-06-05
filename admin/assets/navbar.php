<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<button
    class="btn btn-success mobile-toggle d-lg-none"
    type="button"
    data-bs-toggle="offcanvas"
    data-bs-target="#sidebarMobile" style="margin-left: 10px; margin-top:20px;">

    <i class="bi bi-list"></i>
</button>

<aside class="sidebar-admin d-none d-lg-flex">

    <div>

        <div class="brand-area">
            <h4>UPNVJT Music</h4>
            <p>Admin Dashboard</p>
        </div>

        <ul class="menu-admin">

            <li>
                <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <i class="bi bi-grid-fill"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="mahasiswa.php" class="<?= ($current_page == 'mahasiswa.php') ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i>
                    Users
                </a>
            </li>

            <li>
                <a href="kategori.php" class="<?= ($current_page == 'kategori.php') ? 'active' : ''; ?>">
                    <i class="bi bi-tags-fill"></i>
                    Kategori
                </a>
            </li>

            <li>
                <a href="barang.php" class="<?= ($current_page == 'barang.php') ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam"></i>
                    Barang
                </a>
            </li>

            <li>
                <a href="peminjaman.php" class="<?= ($current_page == 'peminjaman.php') ? 'active' : ''; ?>">
                    <i class="bi bi-journal-check"></i>
                    Peminjaman
                </a>
            </li>

            <li>
                <a href="denda.php" class="<?= ($current_page == 'denda.php') ? 'active' : ''; ?>">
                    <i class="bi bi-clock-history"></i>
                    Denda
                </a>
            </li>

        </ul>

    </div>

    <div class="sidebar-bottom">
        <div class="admin-profile p-3">
            <a href="../logout.php" class="btn btn-danger w-100">
                Logout
            </a>
        </div>
    </div>

</aside>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMobile">

    <div class="offcanvas-header">
        <h5 class="fw-bold text-success">
            UPNVJT Music
        </h5>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas">
        </button>
    </div>

    <div class="offcanvas-body">

        <ul class="menu-admin">

            <li>
                <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <i class="bi bi-grid-fill"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="mahasiswa.php" class="<?= ($current_page == 'mahasiswa.php') ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i>
                    Mahasiswa
                </a>
            </li>

            <li>
                <a href="kategori.php" class="<?= ($current_page == 'kategori.php') ? 'active' : ''; ?>">
                    <i class="bi bi-tags-fill"></i>
                    Kategori
                </a>
            </li>

            <li>
                <a href="barang.php" class="<?= ($current_page == 'barang.php') ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam"></i>
                    Barang
                </a>
            </li>

            <li>
                <a href="peminjaman.php" class="<?= ($current_page == 'peminjaman.php') ? 'active' : ''; ?>">
                    <i class="bi bi-journal-check"></i>
                    Peminjaman
                </a>
            </li>

            <li>
                <a href="denda.php" class="<?= ($current_page == 'denda.php') ? 'active' : ''; ?>">
                    <i class="bi bi-clock-history"></i>
                    Denda
                </a>
            </li>

        </ul>

        <a href="../logout.php" class="btn btn-danger w-100 mt-4">
            Logout
        </a>

    </div>

</div>