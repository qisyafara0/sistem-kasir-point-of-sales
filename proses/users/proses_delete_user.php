<?php
    session_start();
    include "../../config/koneksi.php";

    if (!isset($_SESSION['login']) || $_SESSION['user_role'] !== 'superkasir') {
        header("Location: ../../index.php");
        exit;
    }

    if (isset($_GET['id'])) {

        $id = (int) $_GET['id'];

        // CEK DATA USER
        $cekUser = mysqli_query($koneksi, "
            SELECT user_role FROM tb_users WHERE id_user='$id'
        ");
        $user = mysqli_fetch_assoc($cekUser);

        if ($user['role'] === 'superkasir') {
            echo "<script>
                alert('Superkasir tidak bisa dihapus!');
                window.location.href='../../page/super/kontrol_user.php';
            </script>";
            exit;
        }
        if ($id == $_SESSION['user_id']) {
            echo "<script>
                alert('Tidak bisa menghapus akun sendiri!');
                window.location.href='../../page/super/kontrol_user.php';
            </script>";
            exit;
        }

        // CEK TRANSAKSI
        $cek = mysqli_query($koneksi, "
            SELECT COUNT(*) AS total
            FROM tb_transaksies
            WHERE user_id = '$id'
        ");

        $data = mysqli_fetch_assoc($cek);

        // CONDITION DELETE KASIR
        if ($data['total'] > 0) {
            echo "<script>
                alert('Kasir tidak bisa dihapus (sudah punya transaksi)');
                window.location.href='../../page/super/kontrol_user.php';
            </script>";
            exit;
        }

        $delete = mysqli_query($koneksi, "
            DELETE FROM tb_users WHERE id_user='$id'
        ");

        // REDIRECT DAN NOTIF
        echo "<script>
            alert('".($delete ? "Kasir berhasil dihapus" : "Gagal hapus kasir")."');
            window.location.href='../../page/super/kontrol_user.php';
        </script>";
    }
?>