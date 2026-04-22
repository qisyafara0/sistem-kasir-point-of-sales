<?php

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

    // FILTERING
    // AMBIL DATA DARI PAGE
    $user_id   = mysqli_real_escape_string($koneksi, $_SESSION['user_id']);
    $range     = $_GET['range'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to   = $_GET['date_to'] ?? '';
    $hari_filter = $_GET['hari'] ?? '';

    // PAGINATION
    $limit = (isset($_GET['limit']) && in_array((int)$_GET['limit'], [10,20,50]))
        ? (int)$_GET['limit']
        : 10;

    $page   = max((int)($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    // FILTER DASAR AGAR USER_ID TETAP
    $where = " AND tb_transaksies.user_id = '$user_id'";

    // FILTER BERDASARKAN RANGE HARI DAN BULAN
    if ($range == 'today') {
        $where .= " AND DATE(tb_transaksies.tanggal_transaksi) = CURDATE()";
    } elseif ($range == '7days') {
        $where .= " AND tb_transaksies.tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($range == 'thismonth') {
        $where .= " AND MONTH(tb_transaksies.tanggal_transaksi)=MONTH(CURDATE())
                    AND YEAR(tb_transaksies.tanggal_transaksi)=YEAR(CURDATE())";
    } elseif ($range == 'lastmonth') {
        $where .= " AND MONTH(tb_transaksies.tanggal_transaksi)=MONTH(CURDATE()-INTERVAL 1 MONTH)
                    AND YEAR(tb_transaksies.tanggal_transaksi)=YEAR(CURDATE()-INTERVAL 1 MONTH)";
    }

    // FILTER TANGGAL CUSTOM
    if (
        preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) &&
        preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)
    ) {
        $where .= " AND DATE(tb_transaksies.tanggal_transaksi)
                    BETWEEN '$date_from' AND '$date_to'";
    }

    // FILTER HARI
    if (!empty($hari_filter)) {
        $hari_filter = mysqli_real_escape_string($koneksi, $hari_filter);
        $where .= " AND DAYNAME(tb_transaksies.tanggal_transaksi) = '$hari_filter'";
    }

    // HITUNG TOTAL DATA UNTUK PAGINATION
    $countQuery = "SELECT COUNT(*) AS total
                FROM tb_transaksies
                WHERE 1=1 $where";
    $totalData = mysqli_fetch_assoc(mysqli_query($koneksi, $countQuery))['total'];
    $totalPage = ceil($totalData / $limit);
    
    // HITUNG TOTAL UANG SESUAI PAGINATION
    $sumQuery = "
        SELECT 
            SUM(transaksi.total_transaksi) AS sum_total,
            SUM(transaksi.tunai) AS sum_bayar,
            SUM(transaksi.kembali) AS sum_kembali
        FROM (
            SELECT 
                tb_transaksies.total_transaksi,
                tb_transaksies.tunai,
                tb_transaksies.kembali
            FROM tb_transaksies
            JOIN tb_users ON tb_transaksies.user_id = tb_users.id_user
            $where
            ORDER BY tb_transaksies.tanggal_transaksi DESC
            LIMIT $limit OFFSET $offset
        ) AS transaksi
    ";
    
    $sumResult = mysqli_query($koneksi, $sumQuery);
    $sumData = mysqli_fetch_assoc($sumResult);

    $sum_total      = $sumData['sum_total'] ?? 0;
    $sum_bayar      = $sumData['sum_bayar'] ?? 0;
    $sum_kembali    = $sumData['sum_kembali'] ?? 0;

    // QUERY SELECT DATA TRANSAKSI
    $query = "SELECT 
                id_transaksi,
                tanggal_transaksi,
                total_transaksi,
                tunai,
                kembali,
                status_transaksi
            FROM tb_transaksies
            WHERE 1=1 $where
            ORDER BY tanggal_transaksi DESC
            LIMIT $limit OFFSET $offset";

    $result = mysqli_query($koneksi, $query);

    // HELPER FUNCTION MAKE QUERY URL
    function build_query_params($overrides = []) {
        return http_build_query(array_merge($_GET, $overrides));
    }

    // HELPER FUNCTION UBAH HARI KE INDONESIA
    function hari($tanggal) {
        $hari = date('l', strtotime($tanggal));
        switch ($hari) {
            case 'Monday': return 'Senin';
            case 'Tuesday': return 'Selasa';
            case 'Wednesday': return 'Rabu';
            case 'Thursday': return 'Kamis';
            case 'Friday': return 'Jumat';
            case 'Saturday': return 'Sabtu';
            case 'Sunday': return 'Minggu';
            default: return '-';
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <div class="d-flex">

        <!-- SIDEBAR -->
        <div class="sidebar bg-dark text-white p-3 collapsed" id="sidebar">
            <h4 class="text-center mb-4">Sistem Kasir</h4>
            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard_kasir.php" class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="rekap_kasir.php" class="nav-link text-white active">
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

        <div class="content p-4 expanded" id="content">
            <button class="btn btn-outline-dark mb-3" onclick="toggleSidebar()">☰ Menu</button>
            <h2>Rekap Transaksi Kasir</h2>
            <p>Kasir: <b><?= $_SESSION['nama_user']; ?></b></p>

            <!-- FILTERING -->
            <div class="card mb-4">
                <div class="card-body">

                    <form method="GET" class="row g-2 align-items-end mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Rentang</label>
                            <select name="range" class="form-select">
                                <option value="">Semua</option>
                                <option value="today" <?= $range=='today'?'selected':'' ?>>Hari Ini</option>
                                <option value="7days" <?= $range=='7days'?'selected':'' ?>>7 Hari</option>
                                <option value="thismonth" <?= $range=='thismonth'?'selected':'' ?>>Bulan Ini</option>
                                <option value="lastmonth" <?= $range=='lastmonth'?'selected':'' ?>>Bulan Lalu</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Hari</label>
                            <select name="hari" class="form-select">
                                <option value="">Semua</option>
                                <option value="Monday" <?= $hari_filter=='Monday'?'selected':'' ?>>Senin</option>
                                <option value="Tuesday" <?= $hari_filter=='Tuesday'?'selected':'' ?>>Selasa</option>
                                <option value="Wednesday" <?= $hari_filter=='Wednesday'?'selected':'' ?>>Rabu</option>
                                <option value="Thursday" <?= $hari_filter=='Thursday'?'selected':'' ?>>Kamis</option>
                                <option value="Friday" <?= $hari_filter=='Friday'?'selected':'' ?>>Jumat</option>
                                <option value="Saturday" <?= $hari_filter=='Saturday'?'selected':'' ?>>Sabtu</option>
                                <option value="Sunday" <?= $hari_filter=='Sunday'?'selected':'' ?>>Minggu</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Dari</label>
                            <input type="date" name="date_from" class="form-control"
                                value="<?= ($date_from) ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Sampai</label>
                            <input type="date" name="date_to" class="form-control"
                                value="<?= ($date_to) ?>">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label fw-semibold">Baris</label>
                            <select name="limit" class="form-select">
                                <option value="10" <?= $limit==10?'selected':'' ?>>10</option>
                                <option value="20" <?= $limit==20?'selected':'' ?>>20</option>
                                <option value="50" <?= $limit==50?'selected':'' ?>>50</option>
                            </select>
                        </div>

                        <div class="col-md-1 d-grid">
                            <button type="submit" class="btn btn-success">Filter</button>
                        </div>

                        <div class="col-md-1 d-grid">
                            <a href="rekap_kasir.php" class="btn btn-primary">
                                Reset
                            </a>
                        </div>
                    </form>

                </div>

            </div>

            <!-- TABEL REKAP KASIR DARI TB_TRANSAKSIES -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">

                    <thead class="table-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Tunai</th>
                            <th>Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php
                            $no = $offset + 1;
                            if ($result && mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)): ?>

                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <?= hari($row['tanggal_transaksi']) ?>,
                                    <?= date('d-m-Y H:i', strtotime($row['tanggal_transaksi'])) ?>
                                </td>                            
                                <td>Rp <?= number_format($row['total_transaksi']?? 0)?></td>
                                <td>Rp <?= number_format($row['tunai']?? 0) ; ?></td>
                                <td>Rp <?= number_format($row['kembali'] ?? 0) ; ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $row['status_transaksi']=='paid'?'bg-success':'bg-warning text-dark' ?>">
                                        <?= $row['status_transaksi'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['status_transaksi']=='paid'): ?>
                                        <a href="cek_detail_transaksi.php?id_transaksi=<?= $row['id_transaksi'] ?>" 
                                            class="btn btn-sm btn-primary">
                                            Detail
                                        </a>
                                    <?php endif; ?>
                                        
                                    <?php if ($row['status_transaksi']=='open'): ?>
                                        <a href="../proses/transaksi/proses_delete_transaksi.php?id_transaksi=<?= $row['id_transaksi'] ?>" 
                                            onclick="return confirm('Hapus transaksi ini?')" 
                                            class="btn btn-danger btn-sm">
                                            Hapus
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Data tidak ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                    <tfoot>
                        <tr class="table-secondary fw-bold">
                            <td colspan="2" class="text-center">TOTAL</td>
                            <td>Rp <?= number_format($sum_total) ?></td>
                            <td>Rp <?= number_format($sum_bayar) ?></td>
                            <td>Rp <?= number_format($sum_kembali) ?></td>
                            <td colspan="1"></td>
                            <td colspan="1"></td>
                        </tr>
                    </tfoot>

                </table>

                <?php if ($totalPage > 1): ?>
                    <ul class="pagination justify-content-center">
                        <?php for($i=1;$i<=$totalPage;$i++): ?>
                            <li class="page-item <?= $page==$i?'active':'' ?>">
                                <a class="page-link" href="?<?= build_query_params(['page'=>$i]) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                <?php endif; ?>

            </div>

        </div>

    </div>

    <!-- CONNECT JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script.js"></script>

</body>
</html>