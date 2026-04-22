<?php

    // MENGHUBUNGKAN DENGAN KONEKSI DATABASE
    include '../../config/koneksi.php';

    // SET OUTPUT JSON
    header('Content-Type: application/json');

    // GET KEYWORD DARI URL
    $kode = $_GET['kode_produk'] ?? '';

    // QUERY SEARCH PRODUK
    $query = mysqli_query($koneksi, "SELECT kode_produk, nama_produk, harga_produk FROM tb_produks WHERE kode_produk = '$kode' LIMIT 1");

    // IF CONDITION DATA ADA
    if ($row = mysqli_fetch_assoc($query)) {
        echo json_encode([
            "found" => true,
            "kode_produk" => $row['kode_produk'],
            "nama_produk" => $row['nama_produk'],
            "harga_produk" => $row['harga_produk']
        ]);
    } 
    // ELSE CONDITION DATA TIDAK ADA
    else {
        echo json_encode(["found" => false]);
    }
