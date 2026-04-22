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

    // GET DATA ID DETAIL DAN ID TRANSAKSI
    $id_detail    = $_GET['id'];
    $id_transaksi = $_SESSION['id_transaksi'];

    // AMBIL DATA LAMA
    $query = mysqli_query($koneksi, "
        SELECT produk_id, quantity 
        FROM tb_detail_transaksies 
        WHERE id_detail_transaksi = '$id_detail'
        AND transaksi_id = '$id_transaksi'
    ");

    $data = mysqli_fetch_assoc($query);

    $id_produk = $data['produk_id'];
    $quantity       = $data['quantity'];

    // KEMBALIKAN STOK
    mysqli_query($koneksi, "
        UPDATE tb_produks 
        SET stok_produk = stok_produk + '$quantity'
        WHERE id_produk = '$id_produk'
    ");

    // HAPUS DATA
    mysqli_query($koneksi, "
        DELETE FROM tb_detail_transaksies 
        WHERE id_detail_transaksi = '$id_detail'
    ");

    // REDIRECT
    header("Location: ../../page/form_transaksi.php");
    exit;
?>