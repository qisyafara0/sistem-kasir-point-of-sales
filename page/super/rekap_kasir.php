<?php
    
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

    $range     = $_GET['range'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to   = $_GET['date_to'] ?? '';
    $nama_user = $_GET['nama_user'] ?? '';
    $hari_filter = $_GET['hari'] ?? '';

    // PAGINATION
    $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [10,20,50])
        ? (int)$_GET['limit']
        : 10;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max($page, 1);
    $offset = ($page - 1) * $limit;

    $where = "WHERE 1=1";

    if ($range == 'today') {
        $where .= " AND DATE(tb_transaksies.tanggal_transaksi) = CURDATE()";
    } elseif ($range == '7days') {
        $where .= " AND tb_transaksies.tanggal_transaksi >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($range == 'thismonth') {
        $where .= " AND MONTH(tb_transaksies.tanggal_transaksi)=MONTH(CURDATE())
                    AND YEAR(tb_transaksies.tanggal_transaksi)=YEAR(CURDATE())";
    } elseif ($range == 'lastmonth') {
        $where .= " AND MONTH(tb_transaksies.tanggal_transaksi)=MONTH(CURDATE()-INTERVAL 1 MONTH)
                    AND YEAR(tb_transaksies.tanggal_transaksi)=YEAR(CURDATE()-INTERVAL 1 MONTH)";
    }

    if (
        preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) &&
        preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)
    ) {
        $where .= " AND DATE(tb_transaksies.tanggal_transaksi)
                    BETWEEN '$date_from' AND '$date_to'";
    }

    if (!empty($nama_user)) {
        $nama_user_esc = mysqli_real_escape_string($koneksi, $nama_user);
        $where .= " AND tb_users.nama_user LIKE '%$nama_user_esc%'";
    }

     if (!empty($hari_filter)) {
        $hari_filter = mysqli_real_escape_string($koneksi, $hari_filter);
        $where .= " AND DAYNAME(tb_transaksies.tanggal_transaksi) = '$hari_filter'";
    }

    $countQuery = " SELECT COUNT(*) AS total
                    FROM tb_transaksies
                    JOIN tb_users ON tb_transaksies.user_id = tb_users.id_user
                    $where";
    $countResult = mysqli_query($koneksi, $countQuery);
    $totalData = mysqli_fetch_assoc($countResult)['total'];
    $totalPage = ceil($totalData / $limit);

    $sumQuery = "
        SELECT 
            SUM(t.total_transaksi) AS sum_total,
            SUM(t.tunai) AS sum_bayar,
            SUM(t.kembali) AS sum_kembalian
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
        ) AS t
    ";

    $sumResult = mysqli_query($koneksi, $sumQuery);
    $sumData = mysqli_fetch_assoc($sumResult);

    $sum_total      = $sumData['sum_total'] ?? 0;
    $sum_bayar      = $sumData['sum_bayar'] ?? 0;
    $sum_kembalian  = $sumData['sum_kembalian'] ?? 0;


    function build_query_params($overrides = []) {
        $params = $_GET;
        foreach ($overrides as $k => $v) {
            $params[$k] = $v;
        }
        return http_build_query($params);
    }

    // QUERY SHOW DATA DAFTAR TRANSAKSI SEMUA KASIR 
    $query = "SELECT 
                tb_transaksies.id_transaksi,
                tb_transaksies.tanggal_transaksi,
                tb_transaksies.total_transaksi,
                tb_transaksies.tunai,
                tb_transaksies.kembali,
                tb_transaksies.status_transaksi,
                tb_users.nama_user
            FROM tb_transaksies
            JOIN tb_users ON tb_transaksies.user_id = tb_users.id_user
            $where
            ORDER BY tb_transaksies.tanggal_transaksi DESC
            LIMIT $limit OFFSET $offset";
    $result = mysqli_query($koneksi, $query);

    $query_kasir = "
        SELECT id_user, nama_user 
        FROM tb_users 
        WHERE user_role = 'kasir'
        ORDER BY nama_user ASC
    ";
    $result_nama_kasir = mysqli_query($koneksi, $query_kasir);

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
    <title>Rekap Kasir - Kepala Toko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">
</head>
<body>

    <div class="d-flex">
        <div class="sidebar bg-dark text-white p-3 collapsed" id="sidebar">
            <h4 class="text-center mb-4">Sistem Kasir</h4>
            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard_super_kasir.php" class="nav-link text-white ">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kontrol_produk.php" class="nav-link text-white ">
                        Kontrol Produk
                    </a>
                </li>


                <li class="nav-item">
                    <a href="kontrol_user.php" class="nav-link text-white ">
                        Kelola Kasir
                    </a>
                </li>

                <li class="nav-item">
                    <a href="rekap_kasir.php" class="nav-link text-white active">
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

        <div class="content p-4 expanded" id="content">
            <button class="btn btn-outline-dark mb-3" onclick="toggleSidebar()"> ☰ Menu </button>

            <h3 class="mb-3">Rekap Transaksi Semua Kasir</h3>
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nama Kasir</label>
                            <select name="nama_user" class="form-select">
                                <option value="">Semua Kasir</option>
                                <?php while ($kasir = mysqli_fetch_assoc($result_nama_kasir)): ?>
                                    <option value="<?= ($kasir['nama_user']) ?>"
                                        <?= ($nama_user == $kasir['nama_user']) ? 'selected' : '' ?>>
                                        <?= ($kasir['nama_user']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label fw-semibold">Rentang</label>
                            <select name="range" class="form-select">
                                <option value="">Semua</option>
                                <option value="today" <?= $range=='today'?'selected':'' ?>>Hari Ini</option>
                                <option value="7days" <?= $range=='7days'?'selected':'' ?>>7 Hari</option>
                                <option value="thismonth" <?= $range=='thismonth'?'selected':'' ?>>Bulan Ini</option>
                                <option value="lastmonth" <?= $range=='lastmonth'?'selected':'' ?>>Bulan Lalu</option>
                            </select>
                        </div>

                        <div class="col-md-1">
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
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Daftar Transaksi</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Kasir</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Tunai</th>
                                    <th>Kembalian</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $no = $offset + 1;
                                    if ($result && mysqli_num_rows($result) > 0):
                                        while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= ($row['nama_user']) ?></td>
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
                                                class="btn btn-sm btn-primary">Detail</a>
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
                                    <td colspan="3" class="text-center">TOTAL</td>
                                    <td>Rp <?= number_format($sum_total) ?></td>
                                    <td>Rp <?= number_format($sum_bayar) ?></td>
                                    <td>Rp <?= number_format($sum_kembalian) ?></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>
            </div>                        
            

            <?php if ($totalPage > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i=1; $i<=$totalPage; $i++): ?>
                        <li class="page-item <?= $page==$i?'active':'' ?>">
                            <a class="page-link" href="?<?= build_query_params(['page'=>$i]) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../script.js"></script>                        
    
</body>
</html>
