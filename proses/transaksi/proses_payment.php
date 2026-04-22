<?php

    // MULAI SESSION
    session_start();

    // MENGHUBUNGKAN DENGAN KONEKSI DATABASE
    include "../../config/koneksi.php";

    // CEK TRANSAKSI AKTIF
    if (!isset($_SESSION['id_transaksi'])) {
        header("Location: ../../page/dashboard_kasir.php");
    }

    // VARIABEL BERISI DATA ID TRANSAKSI DAN UANG TUNAI
    $id_transaksi    = $_SESSION['id_transaksi'];
    $tunai = $_POST['tunai'] ?? 0;

    // QUERY HITUNG TOTAL BELANJA
    $query = mysqli_query($koneksi, "SELECT SUM(subtotal) AS total
                                        FROM tb_detail_transaksies
                                        WHERE transaksi_id='$id_transaksi' ");
    $data  = mysqli_fetch_assoc($query);
    $total = $data['total'] ?? 0;

    // VARIABEL TOTAL KEMBALIAN TUNAI
    $kembali = $tunai - $total;

    // VALIDASI UANG TUNAI CUKUP ATAU KURANG 
    if ($tunai < $total) {
        echo "<script>
            alert('Uang tunai kurang!');
            history.back();
        </script>";
    }

    // UPDATE STATUS TRANSAKSI MENJADI PAID
    mysqli_query($koneksi, "
        UPDATE tb_transaksies SET
            total_transaksi = '$total',
            tunai = '$tunai',
            kembali = '$kembali',
            status_transaksi='paid'
        WHERE id_transaksi='$id_transaksi'
    ");

    // HAPUS SESSION TRANSAKSI
    unset($_SESSION['id_transaksi']);

    // REDIRECT CETAK STRUK
    header("Location: proses_cetak_struk.php?id_transaksi=$id_transaksi");
?>