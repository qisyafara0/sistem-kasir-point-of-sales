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

    // GET DATA DARI FORM FILTERING
    $nama_produk = $_GET['nama_produk'] ?? '';
    $kode_produk = $_GET['kode_produk'] ?? '';
    $stok_filter = $_GET['stok'] ?? '';

    // PAGINATION
    $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [10,20,50])
        ? (int)$_GET['limit']
        : 10;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max($page, 1);
    $offset = ($page - 1) * $limit;

    $where = "WHERE 1=1";

    if (!empty($nama_produk)) {
        $nama_produk_esc = mysqli_real_escape_string($koneksi, $nama_produk);
        $where .= " AND nama_produk LIKE '%$nama_produk_esc%'";
    }

    if (!empty($kode_produk)) {
        $kode_produk_esc = mysqli_real_escape_string($koneksi, $kode_produk);
        $where .= " AND kode_produk LIKE '%$kode_produk_esc%'";
    }

    if ($stok_filter === 'habis') {
        $where .= " AND stok_produk = 0";
    } elseif ($stok_filter === 'tersedia') {
        $where .= " AND stok_produk > 0";
    }

    // QUERY SHOW DATA PRODUK 
    $query_produk = "
        SELECT id_produk, kode_produk, nama_produk, stok_produk, harga_produk
        FROM tb_produks
        $where
        ORDER BY kode_produk ASC
        LIMIT $limit OFFSET $offset
    ";

    $result_produk = mysqli_query($koneksi, $query_produk);

    $countQuery = "SELECT COUNT(*) AS total FROM tb_produks $where";
    $countResult = mysqli_query($koneksi, $countQuery);
    $totalData = mysqli_fetch_assoc($countResult)['total'];
    $totalPage = ceil($totalData / $limit);

    function build_query_params($overrides = []) {
        $params = $_GET;
        foreach ($overrides as $k => $v) {
            $params[$k] = $v;
        }
        return http_build_query($params);
    }

    $edit = false;
    $edit_id = '';
    $edit_kode = '';
    $edit_nama = '';
    $edit_stok = '';
    $edit_harga = '';

    if (isset($_GET['edit'])) {
        $edit = true;
        $edit_id = $_GET['edit'];

        $qEditProduk = mysqli_query($koneksi, "
            SELECT * FROM tb_produks 
            WHERE id_produk = '$edit_id'
            LIMIT 1
        ");

        if ($p = mysqli_fetch_assoc($qEditProduk)) {
            $edit_kode   = $p['kode_produk'];
            $edit_nama   = $p['nama_produk'];
            $edit_stok   = $p['stok_produk'];
            $edit_harga = $p['harga_produk'];
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Kepala Toko</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">
</head>
<body >
    
    <div class="d-flex">

        <!-- SIDEBAR -->
        <div class="sidebar bg-dark text-white p-3 collapsed" id="sidebar">
            <h4 class="text-center mb-4">Sistem Kasir</h4>
            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard_super_kasir.php" class="nav-link text-white ">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kontrol_produk.php" class="nav-link text-white active">
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

        <div class="content p-4 expanded" id="content">

            <button class="btn btn-outline-dark mb-3" onclick="toggleSidebar()">
                ☰ Menu
            </button>

            <h3 class="mb-3">Kontrol Produk Toko</h3>

            <div id="produk">

                <!-- FORM FILTERING KALAU LAGI UPDATE INI KE HIDE -->
                <?php if (!$edit): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="mb-3">Filter Produk</h5>
                            <form method="GET" class="row g-2 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Kode Produk</label>
                                    <input type="text" name="kode_produk" class="form-control"
                                        value="<?= ($kode_produk) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Nama Produk</label>
                                    <input type="text" name="nama_produk" class="form-control"
                                        value="<?= ($nama_produk) ?>">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Stok</label>
                                    <select name="stok" class="form-select">
                                        <option value="">Semua</option>
                                        <option value="tersedia" <?= $stok_filter=='tersedia'?'selected':'' ?>>Tersedia</option>
                                        <option value="habis" <?= $stok_filter=='habis'?'selected':'' ?>>Habis</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
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
                                    <a href="kontrol_produk.php" class="btn btn-secondary">
                                        Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- FORM KONTROL PRODUK -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Tambah Produk</h5>
                        <form class="row g-2 align-items-end" action="<?= $edit ? '../../proses/produk/proses_edit_produk.php' : '../../proses/produk/proses_add_produk.php' ?>" method="post">
                            <input type="hidden" name="id_produk" value="<?= $edit_id ?>">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Kode Produk</label>
                                <input type="text" name="kode_produk" class="form-control"
                                    placeholder=" Masukkan Kode Produk" required
                                    value="<?= $edit_kode ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nama Produk</label>
                                <input type="text" name="nama_produk" class="form-control"
                                    placeholder=" Masukkan Nama Produk" required
                                    value="<?= $edit_nama ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tambah Stok</label>
                                <input type="number" name="stok_produk" class="form-control"
                                    placeholder="Masukkan Stok Tambahan" >
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Harga Produk</label>
                                <input type="number" name="harga_produk" class="form-control"
                                    placeholder="Masukkan Harga" required
                                    value="<?= $edit_harga ?>">
                            </div>
                            <?php if ($edit): ?>
                                <div class="col-md-1 d-grid">
                                    <button type="submit" name="update_produk" class="btn btn-warning">
                                        Update
                                    </button>
                                </div>
                                <div class="col-md-1 d-grid">
                                    <a href="kontrol_produk.php" class="btn btn-secondary ">
                                        Batal
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-md-1 d-grid">
                                    <button type="submit" name="tambah_produk" class="btn btn-primary">
                                        Simpan
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- TABLE DATA PRODUK -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Daftar Produk</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama Produk</th>
                                        <th>Stok</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $no = $offset + 1;
                                        if ($result_produk && mysqli_num_rows($result_produk) > 0):
                                            while ($p = mysqli_fetch_assoc($result_produk)):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>

                                        <td><?= $p['kode_produk']; ?></td>
                                        <td><?= $p['nama_produk']; ?></td>
                                        <td class="text-center"><?= $p['stok_produk']; ?></td>
                                        <td>Rp <?= number_format($p['harga_produk']); ?></td>
                                        <td class="text-center">
                                            <a href="?<?= build_query_params(['edit' => $p['id_produk'], 'page'=>1]) ?>"
                                                class="btn btn-sm btn-warning">
                                                Edit
                                            </a>
                                            <a href="../../proses/produk/proses_delete_produk.php?id=<?= $p['id_produk'] ?>"
                                                onclick="return confirm('Hapus produk ini?')"
                                                class="btn btn-sm btn-danger">
                                                    Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Data tidak ditemukan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            
                            <!-- PAGINATION -->
                            <?php if ($totalPage > 1): ?>
                                <nav>
                                    <ul class="pagination justify-content-center mt-3">
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
                </div>

            </div>
        </div>
    </div>

    <!-- CONNECT JAVASCRIPT -->
    <script src="../../script.js"></script>


</body>
</html>
