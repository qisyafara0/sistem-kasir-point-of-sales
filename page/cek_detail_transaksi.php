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

    // VARIABEL BERISI DATA ID TRANSAKSI DAN SESSION USER ID
    $id_transaksi = $_GET['id_transaksi'];
    $user_id      = $_SESSION['user_id'];

    // QUERY CEK TRANSAKSI VALID ATAU TIDAK
    $query_transaksi = " SELECT * FROM tb_transaksies 
                        WHERE id_transaksi = '$id_transaksi' 
                        AND user_id = '$user_id' ";

    // VARIABEL UNTUK MENYIMPAN HASIL QUERY 
    $result_transaksi = mysqli_query($koneksi, $query_transaksi);

    // VARIABEL UNTUK MENYIMPAN HASIL RESULT QUERY 
    $data_transaksi   = mysqli_fetch_assoc($result_transaksi);

    // PENGECEKAN APAKAH VARIABEL DATA_TRANSAKSI VALID ATAU TIDAK MENGGUNAKAN IF CONDITION
    if (!$data_transaksi) {
        echo "<script>
                alert('Transaksi tidak valid!');
                window.location.href = 'rekap_kasir.php';
            </script>";
    }

    // QUERY SELECT DATA DETAIL TRANSAKSI YANG SESUAI ID TRANSAKSI 
    $query_detail = "SELECT tb_detail_transaksies.id_detail_transaksi, 
                            tb_detail_transaksies.produk_id, 
                            tb_detail_transaksies.quantity, tb_detail_transaksies.subtotal, 
                    tb_produks.nama_produk, 
                    tb_produks.harga_produk
                        FROM tb_detail_transaksies
                        JOIN tb_produks ON tb_detail_transaksies.produk_id = tb_produks.id_produk
                        WHERE transaksi_id = '$id_transaksi' ";

    // VARIABEL UNTUK MENYIMPAN HASIL QUERY
    $result_detail = mysqli_query($koneksi, $query_detail);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    
    <div class="container-fluid p-4">
        <h2>Detail Transaksi</h2>

        <!-- CARD INFO, QUICK ACCESS -->
        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <h6 class="mb-1">
                        ID Transaksi: <strong><?= $data_transaksi['id_transaksi']; ?></strong>
                    </h6>

                    <h6 class="mb-1">
                        Tanggal Transaksi: <strong><?= $data_transaksi['tanggal_transaksi']; ?></strong>
                    </h6>
                </div>

                <div>
                    <?php if ($data_transaksi['status_transaksi'] == 'paid'): ?>
                        <a href="../proses/transaksi/proses_cetak_struk.php?id_transaksi=<?= $data_transaksi['id_transaksi'] ?>"
                        target="_blank"
                        class="btn btn-success">
                            Cetak Struk
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>

                        <th>ID Produk</th>
                        <th>Harga</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $grand_total = 0;

                    if (mysqli_num_rows($result_detail) > 0) {
                        while ($row = mysqli_fetch_assoc($result_detail)) {
                            $grand_total += $row['subtotal'];
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= $row['nama_produk']; ?></td>
                        <td>Rp <?= number_format($row['harga_produk'] ?? 0); ?></td>
                        <td class="text-center"><?= $row['quantity']; ?></td>
                        <td>Rp <?= number_format($row['subtotal'] ?? 0); ?></td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Detail pesanan belum tersedia
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">Total</td>
                        <td>Rp <?= number_format($grand_total); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <a href="rekap_kasir.php" class="btn btn-secondary mt-3">
            ← Kembali ke Rekap
        </a>
    </div>
            
    <script src="../script.js"></script>
</body>
</html>
