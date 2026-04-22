<?php
    session_start();
    include "../../config/koneksi.php";

    // CEK ROLE
    if (!isset($_SESSION['login']) || $_SESSION['user_role'] !== 'superkasir') {
        header("Location: ../../index.php");
        exit;
    }

    if (isset($_POST['tambah'])) {

        // GET INPUT
        $username = $_POST['username'];
        $nama     = $_POST['nama_user'];
        $pass     = md5($_POST['password']);
        $role     = 'kasir'; 

        // CEK USERNAME
        $cek = mysqli_query($koneksi, "
            SELECT id_user FROM tb_users
            WHERE username = '$username'
            LIMIT 1
        ");

        // CONDITION JIKA USERNAME SUDAH TERPAKAI
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Username sudah digunakan!');
                    window.location.href='../../page/super/kontrol_user.php';</script>";
            exit;
        }

        // QUERY INSERT
        $insert = mysqli_query($koneksi, "
            INSERT INTO tb_users (username, nama_user, password, user_role)
            VALUES ('$username', '$nama', '$pass', '$role')
        ");

        // REDIRECT
        echo "<script>
            alert('".($insert ? "Kasir berhasil ditambahkan" : "Gagal menambahkan kasir")."');
            window.location.href='../../page/super/kontrol_user.php';
        </script>";
    }
?>