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

    if (isset($_POST['update_produk'])) {

        $id    = $_POST['id_produk'];
        $kode  = $_POST['kode_produk'];
        $nama  = $_POST['nama_produk'];
        $tambah_stok  = max(0, (int)$_POST['stok_produk']); // stok tambahan
        $harga = $_POST['harga_produk'];

        // VALIDASI KODE (TETAP SAMA)
        $cek = mysqli_query($koneksi, "
            SELECT id_produk FROM tb_produks 
            WHERE kode_produk = '$kode' AND id_produk != '$id'
        ");

        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Kode produk sudah digunakan!');
                        window.location.href='../../page/super/kontrol_produk.php';</script>";
            exit;
        }

        // UPDATE DATA PRODUK TERMASUK STOK LAMA DAN STOK TAMBAHAN
        $update = mysqli_query($koneksi, "
            UPDATE tb_produks SET
                kode_produk  = '$kode',
                nama_produk  = '$nama',
                stok_produk  = stok_produk + $tambah_stok,
                harga_produk = '$harga'
            WHERE id_produk = '$id'
        ");

        // REDIRECT DAN NOTIF
        echo "<script>
            alert('".($update ? "Produk berhasil diupdate" : "Gagal update produk")."');
            window.location.href='../../page/super/kontrol_produk.php';
        </script>";
    }
?>