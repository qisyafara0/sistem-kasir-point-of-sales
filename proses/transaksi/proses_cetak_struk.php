<?php

    // MENGHUBUNGKAN DENGAN KONEKSI 
    include "../../config/koneksi.php";

    // CEK TRANSAKSI AKTIF
    if (!isset($_GET['id_transaksi']) || empty($_GET['id_transaksi'])) {
        die("ID transaksi tidak ditemukan");
    }

    // GET ID TRANSAKSI
    $id_transaksi = $_GET['id_transaksi'];

    // QUERY HEADER STRUK
    $query_header_struk = "
        SELECT tb_transaksies.*, tb_users.username 
        FROM tb_transaksies
        JOIN tb_users ON tb_transaksies.user_id = tb_users.id_user
        WHERE tb_transaksies.id_transaksi = '$id_transaksi'
    ";

    $result_header_struk = mysqli_query($koneksi, $query_header_struk);
    $data_header_struk   = mysqli_fetch_assoc($result_header_struk);

    // QUERY INTI STRUK
    $query_struk = "
        SELECT 
            tb_detail_transaksies.quantity,
            tb_detail_transaksies.subtotal,
            tb_produks.nama_produk,
            tb_produks.harga_produk
        FROM tb_detail_transaksies
        JOIN tb_produks 
            ON tb_detail_transaksies.produk_id = tb_produks.id_produk 
        WHERE tb_detail_transaksies.transaksi_id = '$id_transaksi'
    ";
    $result_struk = mysqli_query($koneksi, $query_struk);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Pembayaran</title>
    <!-- STYLE UNTUK STRUK -->
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        html, body {
            width: 58mm;
            margin: 0;
            padding: 0;
            font-family: monospace;
            font-size: 14px;
        }

        .struk {
            width: 58mm;
            padding: 6px;
            box-sizing: border-box;
        }

        .center {
            text-align: center;
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .item {
            display: flex;
            justify-content: space-between;
            line-height: 1.6;
        }

        .small {
            font-size: 13px;
            padding-left: 6px;
        }
    </style>
</head>

<!-- EVENT HANDLER UNTUK PRINT OTOMATIS SAAT LOAD -->
<body onload="window.print()">

    <!-- TEMPLATE STRUK -->
    <div class="struk">

        <div class="center">
            STRUK PEMBAYARAN<br>
            -------------------------
        </div>
        Kasir   : <?= $data_header_struk['username'] ?? '-' ?><br>
        Tanggal : <?= $data_header_struk['tanggal_transaksi'] ?? '-' ?><br>
        <div class="line"></div>
        <?php while ($data = mysqli_fetch_assoc($result_struk)) { ?>
            <div><?= $data['nama_produk']; ?></div>
            <div class="item small">
                <span>
                    <?= number_format($data['harga_produk'], 0, ',', '.'); ?>
                    x <?= $data['quantity']; ?>
                </span>
                <span>
                    <?= number_format($data['subtotal'], 0, ',', '.'); ?>
                </span>
            </div>
        <?php } ?>
        <div class="line"></div>
        <div class="item">
            <strong>Total</strong>
            <strong><?= number_format($data_header_struk['total_transaksi'] ?? 0, 0, ',', '.'); ?></strong>
        </div>
        <div class="item">
            <span>Bayar</span>
            <span><?= number_format($data_header_struk['tunai'] ?? 0, 0, ',', '.'); ?></span>
        </div>
        <div class="item">
            <span>Kembali</span>
            <span><?= number_format($data_header_struk['kembali'] ?? 0, 0, ',', '.'); ?></span>
        </div>
        <div class="center" style="margin-top:10px;">
            Terima Kasih
        </div>
    </div>

    <!-- EVENT HANDLER UNTUK REDIRECT SETELAH PROSES PRINT SELESAI -->
    <script>
        window.onafterprint = function () {
            window.location.href = "../../page/dashboard_kasir.php";
        };
    </script>

</body>
</html>