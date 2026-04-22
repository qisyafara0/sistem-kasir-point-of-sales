<?php

    // MENGHUBUNGKAN DENGAN KONEKSI DATABASE
    include '../../config/koneksi.php';

    // SET OUTPUT JSON
    header('Content-Type: application/json');

    // GET KEYWORD DARI URL
    $keyword = $_GET['keyword'] ?? '';

    $data = [];

    // QUERY SEARCH PRODUK
    $query = mysqli_query($koneksi, "
        SELECT kode_produk, nama_produk, harga_produk
        FROM tb_produks
        WHERE nama_produk LIKE '%$keyword%'
        LIMIT 10
    ");

    // INSERT KE DATA ARRAY
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }

    // UBAH MENJADI JSON
    echo json_encode($data);
