<?php

    // MULAI SESSION 
    session_start();

    // MENGHUBUNGKAN KONEKSI DATABASE
    include "../../config/koneksi.php";

    // CEK TRANSAKSI AKTIF
    if (!isset($_SESSION['id_transaksi'])) {
        header("Location: ../../page/dashboard_kasir.php");
        exit;
    }

    // GET DATA DARI FORM 
    $id_transaksi = $_SESSION['id_transaksi'];
    $id_detail    = $_POST['id_detail_transaksi'];
    $kode_produk  = $_POST['kode_produk'];
    $quantity     = $_POST['quantity'];

    // QUERY SELECT DATA PRODUK SESUAI DENGAN KODE PRODUK
    $query_produk = "SELECT id_produk, harga_produk, stok_produk 
                    FROM tb_produks 
                    WHERE kode_produk = '$kode_produk' 
                    LIMIT 1
    ";
    $result_produk = mysqli_query($koneksi, $query_produk);

    if (mysqli_num_rows($result_produk) == 0) {
        echo "<script>alert('Produk tidak ditemukan');history.back();</script>";
        exit;
    }

    $produk    = mysqli_fetch_assoc($result_produk);
    $id_produk = $produk['id_produk'];
    $harga     = $produk['harga_produk'];
    $stok      = $produk['stok_produk'];

    // GET QUANTITY LAMA
    $quantityLama = mysqli_query($koneksi, "SELECT quantity 
                                            FROM tb_detail_transaksies 
                                            WHERE id_detail_transaksi = '$id_detail'
                                            AND transaksi_id = '$id_transaksi'
    ");

    $old = mysqli_fetch_assoc($quantityLama);
    $quantity_lama = $old['quantity'];

    // HITUNG STOK YANG ADA
    $stok_tersedia = $stok + $quantity_lama;
    // VALIDASI STOK
    if ($quantity > $stok_tersedia) {
        echo "<script>alert('Stok tidak mencukupi!');history.back();</script>";
        exit;
    }
    // HITUNG SUBTOTAL BARU
    $subtotal = $harga * $quantity;

    // QUERY UPDATE DETAIL TRANSAKSI
    mysqli_query($koneksi, "
        UPDATE tb_detail_transaksies SET
            produk_id = '$id_produk',
            quantity  = '$quantity',
            subtotal  = '$subtotal'
        WHERE id_detail_transaksi = '$id_detail'
        AND transaksi_id = '$id_transaksi'
    ");
    // HITUNG STOK TERBARU
    $stok_baru = $stok_tersedia - $quantity;

    // QUERY UPDATE STOK
    mysqli_query($koneksi, "UPDATE tb_produks 
                            SET stok_produk = '$stok_baru'
                            WHERE id_produk = '$id_produk'
    ");

    // REDIRECT
    header("Location: ../../page/form_transaksi.php");
    exit;
?>