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

    // GET DATA ID TRANSAKSI, KODE PRODUK DAN QUANTITY
    $id_transaksi = $_SESSION['id_transaksi'];
    $kode_produk  = $_POST['kode_produk'];
    $quantity          = $_POST['quantity'];

    // QUERY AMBIL DATA PRODUK 
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

    // IF CONDITION CEK STOK
    if ($quantity > $stok) {
        echo "<script>alert('Stok tidak mencukupi!');history.back();</script>";
        exit;
    }

    // HITUNG SUBTOTAL
    $subtotal = $harga * $quantity;

    // QUERY INSERET KE DETAIL TRANSAKSI
    mysqli_query($koneksi, " INSERT INTO tb_detail_transaksies 
                            (transaksi_id, produk_id, quantity, subtotal)
                            VALUES 
                            ('$id_transaksi', '$id_produk', '$quantity', '$subtotal')
    ");

    // HITUNG STOK BARU 
    $stok_baru = $stok - $quantity;
    
    // UPDATE STOK DI TABEL PRODUK
    mysqli_query($koneksi, "
        UPDATE tb_produks 
        SET stok_produk = '$stok_baru'
        WHERE id_produk = '$id_produk'
    ");

    // RDIRECT
    header("Location: ../../page/form_transaksi.php");
    exit;
?>