<?php
    // MULAI SESSION
    session_start();
    // MENGHUBUNGKAN DENGAN KONEKSI DATABASE
    include "../config/koneksi.php";
    // CEK APAKAH TOMBOL LOGIN DITEKAN
    if (isset($_POST['login'])) {
        // AMBIL INPUT USER
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        // PREPARED STATEMENT
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM tb_users 
                                            WHERE username = ? 
                                            AND password = ?"
        );
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // IF CONDITION LOGIN BERHASIL 
        if ($data = mysqli_fetch_assoc($result)) {
            $_SESSION['login']     = true;
            $_SESSION['user_id']   = $data['id_user'];
            $_SESSION['username']  = $data['username'];
            $_SESSION['nama_user'] = $data['nama_user'];
            $_SESSION['user_role'] = $data['user_role'];
            // IF CONDITION REDIRECT SESUAI ROLENYA
            if ($data['user_role'] === 'superkasir') {
                header("Location: ../page/super/dashboard_super_kasir.php");
            } else {
                header("Location: ../page/dashboard_kasir.php");
            }
            exit;
        } // ELSE CONDITION LOGIN GAGAL
        else{
            $_SESSION['error'] = "Username atau password salah";
                header("Location: ../index.php");
            exit;
        }        
    }
?>





