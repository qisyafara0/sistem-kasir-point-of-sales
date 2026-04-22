<?php

    // MULAI SESSION
    session_start();

    // MENGHUBUNGKAN KONEKSI DATABASE
    include "../../config/koneksi.php";

    // CEK ROLE
    if (!isset($_SESSION['login']) || $_SESSION['user_role'] !== 'superkasir') {
        header("Location: ../../index.php");
        exit;
    }

    if (isset($_POST['tambah_produk'])) {
        $kode  = $_POST['kode_produk'];
        $nama  = $_POST['nama_produk'];
        $stok  = $_POST['stok_produk'];
        $harga = $_POST['harga_produk'];
        // CEK KODE
        $cek = mysqli_query($koneksi, "
            SELECT id_produk FROM tb_produks 
            WHERE kode_produk = '$kode'
        ");
        // CONDITION JIKA KODE PRODUK SUDAH TERPAKAI
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Kode produk sudah digunakan!');
                    window.location.href='../../page/super/kontrol_produk.php';
            </script>";
            exit;
        }
        // INSERT NEW DATA PRODUK
        $insert = mysqli_query($koneksi, "
            INSERT INTO tb_produks (kode_produk, nama_produk, stok_produk, harga_produk)
            VALUES ('$kode', '$nama', '$stok', '$harga')
        ");
        // REDIRECT DAN NOTIF
        echo "<script>
            alert('".($insert ? "Produk berhasil ditambahkan" : "Gagal menambahkan produk!")."');
            window.location.href='../../page/super/kontrol_produk.php';
        </script>";
    }
?>