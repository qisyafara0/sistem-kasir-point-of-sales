<?php

    // MULAI SESSION 
    session_start();

    // MENGHUBUNGKAN KONEKSI DATABASE
    include "../../config/koneksi.php";

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

    // QUERY INSERT KE TABEL TRANSAKSI
    $query = "INSERT INTO tb_transaksies (user_id, tanggal_transaksi, status_transaksi) 
                VALUES ('".$_SESSION['user_id']."', NOW(), 'open')
    ";
    
    $result = mysqli_query($koneksi, $query);

    // AMBIL ID TRANSAKSI
    $_SESSION['id_transaksi'] = mysqli_insert_id($koneksi);
    
    // REDIRECT KE FORM TRANSAKSI
    header("Location: ../../page/form_transaksi.php");
?>