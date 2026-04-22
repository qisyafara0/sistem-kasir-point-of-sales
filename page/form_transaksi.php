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

    // CEK TRANSAKSI AKTIF
    if (!isset($_SESSION['id_transaksi'])) {
        header("Location: dashboard_kasir.php");
    }
    
    // AMBIL DATA DARI SESSION
    $id_transaksi = $_SESSION['id_transaksi'];
    $username     = $_SESSION['username'];
    $nama_kasir = $_SESSION['nama_user'];

    // QUERY SELECT DATA DETAIL TRANSAKSI YANG SESUAI ID TRANSAKSI 
    $query = "SELECT tb_detail_transaksies.*, tb_produks.nama_produk, tb_produks.harga_produk 
                FROM tb_detail_transaksies
                JOIN tb_produks ON tb_detail_transaksies.produk_id = tb_produks.id_produk
                WHERE tb_detail_transaksies.transaksi_id = '$id_transaksi'";
    
    // VARIABEL UNTUK MENYIMPAN HASIL QUERY
    $result = mysqli_query($koneksi, $query);

    // VARIABEL UNTUK MODE EDIT
    $edit_id = null;
    $edit_kode = '';
    $edit_quantity  = '';
    $edit_nama_produk  = '';

    // PENGECEKAN PARAMETER EDIT DENGAN IF CONDITION
    if (isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];

        // AMBIL DATA DETAIL TRANSAKSI YANG AKAN DI UPDATE BY ID 
        $qEdit = mysqli_query($koneksi, "
            SELECT 
                tb_detail_transaksies.id_detail_transaksi,
                tb_detail_transaksies.quantity,
                tb_produks.kode_produk,
                tb_produks.nama_produk,
                tb_produks.harga_produk
            FROM tb_detail_transaksies
            JOIN tb_produks ON tb_detail_transaksies.produk_id = tb_produks.id_produk
            WHERE tb_detail_transaksies.id_detail_transaksi = '$edit_id'
            AND tb_detail_transaksies.transaksi_id = '$id_transaksi'
            LIMIT 1
        ");

        // ISI KE FORM EDITNYA
        if ($rowEdit = mysqli_fetch_assoc($qEdit)) {
            $edit_kode = $rowEdit['kode_produk'];
            $edit_quantity  = $rowEdit['quantity'];
            $edit_nama_produk = $rowEdit['nama_produk'];
            $edit_harga_produk = $rowEdit['harga_produk'];
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Aktif</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <div class="container-fluid p-4">

        <!-- INFORMASI TRANSAKSI -->
        <div>
            <div class="mb-4">
                <h2> Pesanan Aktif </h2>
                <p class="text-muted mb-0">
                    Kasir: <b><?= $nama_kasir ?></b>
                </p>
            </div>

            <div class="row mb-4">

                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="mb-1">ID Transaksi</h4>
                            <h3 class="text-primary mb-0"><?= $id_transaksi ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 text-center">
                        <div class="card-body ">
                            <small class="text-muted">Waktu Sekarang</small>
                            <h5 id="clock">--:--:--</h5>
                            <small id="date"></small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border-danger">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="text-danger mb-1">Batalkan Transaksi</h6>
                            </div>
                            <a href="../proses/transaksi/proses_delete_transaksi.php?id_transaksi=<?= $id_transaksi ?>"
                            class="btn btn-danger mt-3"
                            onclick="return confirm('Yakin ingin membatalkan transaksi ini?')">
                                Batalkan
                            </a>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- FORM TRANSAKSI -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3"> Input Pesanan </h5>

                <form method="post" action="<?= $edit_id 
                    ? '../proses/detail_transaksi/proses_edit_detail_transaksi.php' 
                    : '../proses/detail_transaksi/proses_add_detail_transaksi.php' ?>" 
                    class="row g-2 align-items-end">

                    <input type="hidden" name="transaksi_id" value="<?= $id_transaksi ?>">

                    <input type="hidden" name="id_detail_transaksi" value="<?= $edit_id ?>">

                    <div class="col-md-3">
                        <label class="form-label">Kode Produk</label>
                        <input type="text"
                            id="kode_produk"
                            name="kode_produk"
                            class="form-control"
                            onkeyup="searchByKode()"
                            value="<?= $edit_kode ?>"
                            required>
                    </div>
                    
                    <div class="col-md-4 position-relative">
                        <label class="form-label">Nama Produk</label>
                        <input type="text"
                            id="nama_produk"
                            name="nama_produk"
                            class="form-control"
                            onkeyup="searchNamaProduk()"
                            autocomplete="off"
                            value="<?= $edit_nama_produk ?>"
                            required>
                        <div id="dropdown_produk" 
                                class="list-group position-absolute w-100" 
                                style="z-index: 1000;">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Harga</label>
                        <input type="number" id="harga_produk" class="form-control" value="<?= $edit_harga_produk?>" readonly>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Quantity</label>
                        <input type="number"
                            name="quantity"
                            class="form-control"
                            min="1"
                            value="<?= $edit_quantity ?>"
                            required>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button class="btn <?= $edit_id ? 'btn-warning' : 'btn-success' ?>">
                            <?= $edit_id ? 'Update' : '+ Tambah' ?>
                        </button>
                    </div>

                </form>

            </div>
        </div>

        <!-- TABEL SEMUA PESANAN -->
        <div class="card">
            <div class="card-body">

                <h5 class="mb-3"> Daftar Pesanan</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">

                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                $no = 1;
                                $total = 0;
                                while ($data = mysqli_fetch_assoc($result)) {
                                    $total += $data['subtotal'];
                            ?>
                            <tr>
                                <td> <?= $no++ ?></td>
                                <td><?= $data['nama_produk'] ?></td>
                                <td><?= number_format($data['harga_produk']) ?></td>
                                <td class="text-center"><?= $data['quantity'] ?></td>
                                <td><?= number_format($data['subtotal']) ?></td>
                                <td class="text-center">
                                    <a href="?edit=<?= $data['id_detail_transaksi']; ?>" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>
                                    <a href="../proses/detail_transaksi/proses_delete_detail_transaksi.php?id=<?= $data['id_detail_transaksi'] ?>" onclick="return confirm('Hapus pesanan ini?')" class="btn btn-danger btn-sm">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>

                    </table>

                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <h5>Total: <b class="text-success">Rp <?= number_format($total) ?></b></h5>
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalPayment">
                        Bayar
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- MODAL POP UP HITUNG PAYMENT -->
    <div class="modal fade" id="modalPayment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../proses/transaksi/proses_payment.php" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Total Belanja</label>
                            <input type="text" id="total_belanja"
                                class="form-control"
                                value="<?= $total ?>"
                                readonly>
                        </div>
                        <div class="mb-3">
                            <label>Uang Tunai</label>
                            <input type="number" id="tunai"
                                name="tunai"
                                class="form-control"
                                min="<?= $total ?>"
                                onkeyup="hitungKembalian()"
                                required>
                        </div>

                        <div class="mb-3">
                            <label>Kembalian</label>
                            <input type="text"
                                id="kembali"
                                class="form-control"
                                readonly>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" id="btnCetak"
                            class="btn btn-success"
                            disabled>
                            Cetak Struk
                        </button>
                    </div>
                </form>
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
