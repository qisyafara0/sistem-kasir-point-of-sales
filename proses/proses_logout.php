<?php

    // SESSION MULAI
    session_start();

    // HAPUS SESSION
    session_destroy();

    // NONTIFIKASI DAN REDIRECT AFTER LOGOUT
    echo "<script>
        alert('Berhasil Logout!');
        window.location.href = '../index.php';
    </script>";
?>
