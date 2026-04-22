<?php
    
    date_default_timezone_set('Asia/Jakarta');

    // MULAI SESSION
    session_start();

    // MENGHUBUNGKAN DENGAN KONEKSI 
    include "../../config/koneksi.php";

    // CEK LOGIN 
    if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
        echo "<script>
                alert('Silakan login terlebih dahulu!');
                window.location.href = '../index.php';
            </script>";
        exit;
    }

    // CEK ROLE 
    if ($_SESSION['user_role'] === 'kasir') {
        echo "<script>
                alert('Akses ditolak! Anda adalah kasir.');
                window.location.href = '../dashboard_kasir.php';
            </script>";
        exit;
    }

    // GET DATETIME HARI INI
    $today = date('Y-m-d');

    // VARIABEL BERISI QUERY MENGHITUNG TOTAL TRANSAKSI HARI INI
    $total_pendapatan = mysqli_fetch_assoc(mysqli_query($koneksi,"
        SELECT SUM(total_transaksi) total 
        FROM tb_transaksies 
        WHERE status_transaksi='paid'
        AND DATE(tanggal_transaksi)='$today'
    "))['total'];
    
    // QUERY PRODUK STOK MENIPIS & HABIS (<=10)
    $q_stok_menipis = mysqli_query($koneksi, "
        SELECT nama_produk, stok_produk 
        FROM tb_produks
        WHERE stok_produk <= 10
        ORDER BY stok_produk ASC
    ");

    // QUERY TOTAL SEMUA PRODUK
    $query_total_produk = "SELECT COUNT(*) AS total FROM tb_produks";
    $result_total_produk = mysqli_query($koneksi, $query_total_produk);
    $totalProduk = mysqli_fetch_assoc($result_total_produk)['total'];

    // QUERY TOTAL STOK SEMUA PRODUK
    $qTotalStok = mysqli_query($koneksi, "SELECT SUM(stok_produk) AS total_stok FROM tb_produks");
    $totalStok = mysqli_fetch_assoc($qTotalStok)['total_stok'] ?? 0;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Kepala Toko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    
    <div class="d-flex">

        <!-- SIDEBAR -->
        <div class="sidebar bg-dark text-white p-3" id="sidebar">
            <h4 class="text-center mb-4">Sistem Kasir</h4>
            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard_super_kasir.php" class="nav-link text-white active">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kontrol_produk.php" class="nav-link text-white">
                        Kontrol Produk
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kontrol_user.php" class="nav-link text-white">
                        Kontrol Kasir
                    </a>
                </li>
                <li class="nav-item">
                    <a href="rekap_kasir.php" class="nav-link text-white">
                        Rekap Transaksi
                    </a>
                </li>

                

                <li class="nav-item">
                    <a href="../../proses/proses_logout.php" class="nav-link text-white">
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <div class="content p-4" id="content">
            <button class="btn btn-outline-dark mb-3" onclick="toggleSidebar()">
                ☰ Menu
            </button>

            <h2>Dashboard Kepala Toko</h2>
            <p>Selamat datang, <b><?= $_SESSION['nama_user'] ?></b></p>

            <!-- QUICK ACCESS -->
            <div class="row g-4 mb-4">

                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <small class="text-muted">Waktu Sekarang</small>
                            <h5 id="clock">--:--:--</h5>
                            <small  id="date"></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <a href="kontrol_produk.php" class="text-decoration-none">
                        <div class="card shadow-sm text-center h-100">
                            <div class="card-body">
                                <h5 class="card-title">Kelola Produk</h5>
                                <p class="card-text">Kelola data produk</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="rekap_kasir.php" class="text-decoration-none">
                        <div class="card shadow-sm text-center h-100">
                            <div class="card-body">
                                <h5 class="card-title">Rekap Kasir</h5>
                                <p class="card-text">Laporan transaksi kasir</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="kontrol_kasir.php" class="text-decoration-none">
                        <div class="card shadow-sm text-center h-100">
                            <div class="card-body">
                                <h5 class="card-title">Kelola User</h5>
                                <p class="card-text">Manajemen akun kasir & user</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- CARD STATISTIK -->
            <div>
                <h5 class="mb-3">Statistik Hari Ini</h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <a href="kontrol_produk.php" class="text-decoration-none text-dark">
                            <div class="card shadow-sm text-center h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Total Produk</h6>
                                    <h3><?= $totalProduk ?></h3>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="kontrol_produk.php" class="text-decoration-none text-dark">
                            <div class="card shadow-sm text-center h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Total Stok</h6>
                                    <h3><?= $totalStok ?></h3>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="rekap_kasir.php" class="text-decoration-none text-dark text-center">
                            <div class="card shadow-sm text-center h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Pendapatan Hari Ini</h6>    
                                    <h3>Rp <?= number_format($total_pendapatan ?? 0) ?></h3>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- TABEL STOK PRODUK -->
            <h5 class="mb-3">Stok Produk Menipis & Habis</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">

                    <thead class="table-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($q_stok_menipis && mysqli_num_rows($q_stok_menipis) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while($row = mysqli_fetch_assoc($q_stok_menipis)) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>

                                    <td><?= $row['nama_produk'] ?></td>

                                    <td class="text-center"><?= $row['stok_produk'] ?></td>

                                    <td class="text-center">
                                        <?php if ($row['stok_produk'] == 0): ?>
                                            <span class="badge bg-danger">Habis</span>
                                        <?php elseif ($row['stok_produk'] <= 5): ?>
                                            <span class="badge bg-warning text-dark">Menipis</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">Hampir Habis</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Tidak ada produk yang stoknya menipis / habis
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    <!-- CONNECT JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../script.js"></script>

    <!-- FUNCTION REAL DATE TIME -->
    <script>
        function updateClock() {
            const now = new Date();

            const optionsTime = {
                timeZone: 'Asia/Jakarta',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };

            const optionsDate = {
                timeZone: 'Asia/Jakarta',
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };

            const timeFormatter = new Intl.DateTimeFormat('id-ID', optionsTime);
            const dateFormatter = new Intl.DateTimeFormat('id-ID', optionsDate);

            document.getElementById('clock').innerHTML = timeFormatter.format(now);
            document.getElementById('date').innerHTML = dateFormatter.format(now);
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>

</body>
</html>
