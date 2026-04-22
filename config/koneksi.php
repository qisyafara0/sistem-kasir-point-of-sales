<?php
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $data = 'sistem-kasir-point-of-sale';

    $koneksi = mysqli_connect($host, $user, $pass, $data);

    if (!$koneksi){
        die("Koneksi Gagal" . mysqli_connect_error());
    }
?>