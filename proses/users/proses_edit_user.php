<?php
    session_start();
    include "../../config/koneksi.php";

    if (!isset($_SESSION['login']) || $_SESSION['user_role'] !== 'superkasir') {
        header("Location: ../../index.php");
        exit;
    }

    if (isset($_POST['update'])) {
        $id       = $_POST['id_user'];
        $username = $_POST['username'];
        $nama     = $_POST['nama_user'];
        // CEK USERNAME DUPLIKAT
        $cek = mysqli_query($koneksi, "
            SELECT id_user FROM tb_users
            WHERE username = '$username'
            AND id_user != '$id'
            LIMIT 1
        ");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Username sudah digunakan!');
                window.location.href='../../page/super/kontrol_user.php';
            </script>";
            exit;
        }
        // QUERY UPDATE 
        // CEK CONDITION APAKAH PASS DI UPDATE ATAU TIDAK
        if (!empty($_POST['password'])) {
            $pass = md5($_POST['password']);
            $update = mysqli_query($koneksi, "
                UPDATE tb_users SET
                    username='$username',
                    nama_user='$nama',
                    password='$pass'
                WHERE id_user='$id'
            ");
        } else {
            $update = mysqli_query($koneksi, "
                UPDATE tb_users SET
                    username='$username',
                    nama_user='$nama'
                WHERE id_user='$id'
            ");
        }
        // REDIRECT DAN NOTIF
        echo "<script>
            alert('".($update ? "Kasir berhasil diupdate" : "Gagal update kasir")."');
            window.location.href='../../page/super/kontrol_user.php';
        </script>";
    }
?>