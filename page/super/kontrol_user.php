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

    $username = $_GET['username'] ?? '';
    $nama_user = $_GET['nama_user'] ?? '';

    $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [10,20,50]) ? (int)$_GET['limit'] : 10;
    $page = max((int)($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    $where = "WHERE user_role = 'kasir'";

    if ($username) {
        $u = mysqli_real_escape_string($koneksi, $username);
        $where .= " AND username LIKE '%$u%'";
    }

    if ($nama_user) {
        $n = mysqli_real_escape_string($koneksi, $nama_user);
        $where .= " AND nama_user LIKE '%$n%'";
    }

    // QUERY SHOW DATA 
    $query = "SELECT id_user, username, nama_user
        FROM tb_users
        WHERE user_role = 'kasir'
        ORDER BY username ASC
        LIMIT $limit OFFSET $offset
    ";
    $result = mysqli_query($koneksi, $query);

    $count = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_users $where");
    $totalData = mysqli_fetch_assoc($count)['total'];
    $totalPage = ceil($totalData / $limit);

    $edit = false;
    $edit_id = $edit_username = $edit_nama = '';

    if (isset($_GET['edit'])) {
        $edit = true;
        $id = (int)$_GET['edit'];

        $q = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE id_user=$id LIMIT 1");
        if ($u = mysqli_fetch_assoc($q)) {
            $edit_id = $u['id_user'];
            $edit_username = $u['username'];
            $edit_nama = $u['nama_user'];
        }
    }

    function build_query_params($overrides = []) {
        return http_build_query(array_merge($_GET, $overrides));
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
                    <a href="kontrol_produk.php" class="nav-link text-white ">
                        Kontrol Produk
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kontrol_user.php" class="nav-link text-white active">
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

            <h3 class="mb-3">Kontrol Kasir</h3>

            <div id="user">

                <!-- FILTERING -->
                <?php if (!$edit): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="mb-3">Filter Kasir</h5>
                            <form method="get" class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Username</label>
                                    <input type="text" name="username" class="form-control"  value="<?= $username ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Nama Kasir</label>
                                    <input type="text" name="nama_user" class="form-control" value="<?= $nama_user ?>">
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
                                    <button class="btn btn-success">Filter</button>
                                </div>
                                <div class="col-md-1 d-grid">
                                    <a href="kontrol_user.php" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- FORM KONTROL USER -->
                <div class="card mb-4">

                    <div class="card-body">
                        <h5 class="mb-3"><?= $edit ? 'Edit Kasir' : 'Tambah Kasir' ?></h5>

                        <form action="<?= $edit ? '../../proses/users/proses_edit_user.php' : '../../proses/users/proses_add_user.php' ?>" method="post" class="row g-2 align-items-end">
                            <input type="hidden" name="id_user" value="<?= $edit_id ?>">

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan Username" required value="<?= $edit_username ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nama Kasir</label>
                                <input type="text" name="nama_user" class="form-control" placeholder="Masukkan Nama Kasir" required value="<?= $edit_nama ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    Password <?= $edit ? '(Kosongkan jika tidak diubah)' : '' ?>
                                </label>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan Password" <?= $edit ? '' : 'required' ?>>
                            </div>

                            <?php if ($edit): ?>
                                <div class="col-md-1 d-grid">
                                    <button name="update" class="btn btn-warning">Update</button>
                                </div>
                                <div class="col-md-1 d-grid">
                                    <a href="kontrol_user.php" class="btn btn-secondary">Batal</a>
                                </div>
                            <?php else: ?>
                                <div class="col-md-1 d-grid">
                                    <button name="tambah" class="btn btn-primary">Simpan</button>
                                </div>
                            <?php endif; ?>
                        </form>

                    </div>
                </div>
                
                <!-- TABLE DATA USER -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Daftar Kasir</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $no = $offset + 1;
                                if ($result && mysqli_num_rows($result) > 0):
                                    while ($kasir = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= $kasir['username'] ?></td>
                                    <td><?= $kasir['nama_user'] ?></td>
                                    <td class="text-center">
                                        <a href="?<?= build_query_params(['edit'=>$kasir['id_user'],'page'=>1]) ?>"
                                            class="btn btn-sm btn-warning">
                                            Edit
                                        </a>
                                        <a href="../../proses/users/proses_delete_user.php?id=<?= $kasir['id_user'] ?>"
                                            onclick="return confirm('Hapus kasir ini?')"
                                            class="btn btn-sm btn-danger">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Data tidak ditemukan</td>
                                </tr>
                                <?php endif; ?>
                                </tbody>
                                </table>


                            <!-- PAGINATION -->
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

            </div>
        </div>
    </div>

    <!-- CONNECT JAVASCRIPT -->
    <script src="../../script.js"></script>


</body>
</html>
