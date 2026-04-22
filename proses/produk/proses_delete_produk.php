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

    if (isset($_GET['id'])) {

        $id = (int) $_GET['id'];

        try {
            // QUERY DELETE DATA PRODUK
            mysqli_query($koneksi, "
                DELETE FROM tb_produks WHERE id_produk = '$id'
            ");

            echo "<script>
                alert('Produk berhasil dihapus');
                window.location.href='../../page/super/kontrol_produk.php';
            </script>";

        } catch (mysqli_sql_exception $e) {

            // CONDITION APABILA DATA PRODUK SUDAH TERPAKAI TRANSAKSI
            if ($e->getCode() == 1451) {
                echo "<script>
                    alert('Produk tidak bisa dihapus (dipakai di transaksi)');
                    window.location.href='../../page/super/kontrol_produk.php';
                </script>";
            } else {
                echo "<script>
                    alert('Gagal hapus produk');
                    window.location.href='../../page/super/kontrol_produk.php';
                </script>";
            }
        }
    }
?>