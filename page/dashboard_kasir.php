<?php

    date_default_timezone_set('Asia/Jakarta');
    // MULAI SESSION
    session_start();

    // MENGHUBUNGKAN DENGAN KONEKSI 
    include "../config/koneksi.php";

    // CEK LOGIN 
    if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
        echo "<script>
                alert('Silakan login terlebih dahulu!');
                window.location.href = '../index.php';
            </script>";
        exit;
    }

    // CEK ROLE 
    if ($_SESSION['user_role'] === 'superkasir') {
        echo "<script>
                alert('Akses ditolak! Anda adalah superkasir.');
                window.location.href = '../page/super/dashboard_super_kasir.php';
            </script>";
        exit;
    }
    
    // GET DATETIME HARI INI
    $today = date('Y-m-d');

    // VARIABEL BERISI QUERY MENGHITUNG TOTAL TRANSAKSI HARI INI
    $q_total_transaksi = mysqli_fetch_assoc(mysqli_query($koneksi,"
        SELECT COUNT(*) total 
        FROM tb_transaksies 
        WHERE user_id='{$_SESSION['user_id']}' 
        AND DATE(tanggal_transaksi)='$today'
    "))['total'];

    // VARIABEL BERISI QUERY MENGHITUNG TOTAL TRANSAKSI HARI INI
    $q_total_pendapatan = mysqli_fetch_assoc(mysqli_query($koneksi,"
        SELECT SUM(total_transaksi) total 
        FROM tb_transaksies 
        WHERE user_id='{$_SESSION['user_id']}' 
        AND status_transaksi='paid'
        AND DATE(tanggal_transaksi)='$today'
    "))['total'];

    // VARIABEL BERISI QUERY MENGHITUNG TOTAL TRNASAKSI OPEN HARI INI
    $q_open = mysqli_fetch_assoc(mysqli_query($koneksi,"
        SELECT COUNT(*) total 
        FROM tb_transaksies 
        WHERE user_id='{$_SESSION['user_id']}' 
        AND status_transaksi='open'
    "))['total'];

    // VARIABEL BERISI QUERY MENGHITUNG TOTAL TRNASAKSI PAID HARI INI
    $q_paid = mysqli_fetch_assoc(mysqli_query($koneksi,"
        SELECT COUNT(*) total 
        FROM tb_transaksies 
        WHERE user_id='{$_SESSION['user_id']}' 
        AND status_transaksi='paid'
        AND DATE(tanggal_transaksi)='$today'

    "))['total'];

    // VARIABEL BERISI QUERY MENGHITUNG TOTAL TRNASAKSI TERAKHIR HARI INI
    $q_last = mysqli_query($koneksi,"
        SELECT id_transaksi, total_transaksi, status_transaksi, tanggal_transaksi
        FROM tb_transaksies
        WHERE user_id='{$_SESSION['user_id']}'
        ORDER BY tanggal_transaksi DESC
        LIMIT 4
    ");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="d-flex">

        <!-- SIDEBER -->
        <div class="sidebar bg-dark text-white p-3" id="sidebar">
            <h4 class="text-center mb-4">Sistem Kasir</h4>
            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard_kasir.php" class="nav-link text-white active">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="rekap_kasir.php" class="nav-link text-white">
                        Rekap Kasir
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../proses/proses_logout.php" class="nav-link text-white">
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <div class="content p-4" id="content">

            <button class="btn btn-outline-dark mb-3" onclick="toggleSidebar()">
                ☰ Menu
            </button>

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h2 class="mb-0">Dashboard Kasir</h2>
                    <p class="mb-0">Selamat datang, <b><?= $_SESSION['nama_user'] ?></b></p>
                </div>

                <!-- TOMBOL ADD NEW TRANSAKSI -->
                <a href="../proses/transaksi/proses_add_new_transaksi.php" class="btn btn-primary fw-semibold">
                    + Transaksi Baru
                </a>

            </div>
            
            <!-- QUICK ACCESS, INFO -->
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

                <div class="col-md-2">
                    <a href="rekap_kasir.php" class="text-decoration-none text-dark">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h3><?= $q_total_transaksi ?></h3>
                                <small class="text-muted">Transaksi Hari Ini</small>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-2">
                    <a href="rekap_kasir.php" class="text-decoration-none text-dark">
                        <div  class="card shadow-sm h-100">
                            <div class="card-body">
                                <h3><?= $q_open ?></h3>
                                <small class="text-muted">Transaksi Open</small>
                            </div>
                        </div>
                    </a>
                </div>


                <div class="col-md-2">
                    <a href="rekap_kasir.php" class="text-decoration-none text-dark">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h3><?= $q_paid ?></h3>
                                <small class="text-muted">Transaksi Paid</small>
                            </div>
                        </div>  
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="rekap_kasir.php" class="text-decoration-none text-dark">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h3>Rp <?= number_format($q_total_pendapatan ?? 0); ?></h3>
                                <small class="text-muted">Pendapatan Hari Ini</small>
                            </div>
                        </div>
                    </a>
                </div>

            </div>

            <!-- TABEL TRANSAKSI TERAKHIR LIMIT 4 -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3">Transaksi Terakhir</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">

                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                    <th>Cetak Struk</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($q_last && mysqli_num_rows($q_last) > 0): ?>
                                    <?php $no = 1; ?>
                                    <?php while($row = mysqli_fetch_assoc($q_last)) : ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>

                                            <td>
                                                Rp <?= number_format($row['total_transaksi'] ?? 0) ?>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge <?= $row['status_transaksi']=='paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                    <?= $row['status_transaksi'] ?>
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <?= date('H:i', strtotime($row['tanggal_transaksi'])) ?>
                                            </td>

                                            <td class="text-center">
                                                <?php if ($row['status_transaksi']=='paid'): ?>
                                                    <a href="../proses/transaksi/proses_cetak_struk.php?id_transaksi=<?= $row['id_transaksi'] ?>" 
                                                    class="btn btn-sm btn-primary">
                                                        Cetak
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>

                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Data tidak ditemukan
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONNECT JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script.js"></script>
    
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
