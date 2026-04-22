<?php

    // MULAI SESSION
    session_start();

    // MENGHUBUNGKAN DENGAN KONEKSI 
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

    // GET DATA ID_TRANSAKSI DAN USER_ID
    $id_transaksi = $_GET['id_transaksi'];
    $user_id      = $_SESSION['user_id'];

    // QUERY VALIDASI DATA TRANSAKSI
    $cek = mysqli_query($koneksi, "SELECT id_transaksi FROM tb_transaksies 
                                    WHERE id_transaksi = '$id_transaksi'
                                    AND user_id = '$user_id' AND status_transaksi = 'open' 
    ");

    // IF CONDITION TIDAK VALID
    if (mysqli_num_rows($cek) == 0) {
        echo "<script>
                alert('Transaksi tidak bisa dihapus!');
                window.location.href = '../../page/rekap_kasir.php';
            </script>";
        exit;
    }

    // QUERY DELETE DATA DETAIL TRANSAKSI 
    mysqli_query($koneksi, " DELETE FROM tb_detail_transaksies 
                            WHERE transaksi_id = '$id_transaksi'");

    // QUERY DELETE DATA TRANSAKSI 
    mysqli_query($koneksi, "DELETE FROM tb_transaksies 
                            WHERE id_transaksi = '$id_transaksi' ");

    // NOTIF DAN REDIRECT 
    echo "<script>
            alert('Transaksi berhasil dihapus');
            window.location.href = '../../page/rekap_kasir.php';
        </script>";
?>



