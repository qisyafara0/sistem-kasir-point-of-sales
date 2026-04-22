<?php

    // MULAI SESSION
    session_start();

    // MENGHUBUNGKAN DENGAN KONEKSI 
    include "config/koneksi.php";

    // CEK ROLE
    if (isset($_SESSION['login'])) {
        if ($_SESSION['user_role'] === 'superkasir') {
            header("Location: page/super/dashboard_super_kasir.php");
        } else {
            header("Location: page/dashboard_kasir.php");
        }
        exit;
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | Sistem Kasir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container min-vh-100 d-flex justify-content-center align-items-center">
    <div class="card shadow border-0" style="width: 380px;">
        <div class="card-body p-4">

            <!-- HEADER -->
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1">Login Kasir</h4>
            </div>

            <hr>

            <!-- ALERT ERROR -->
            <?php if (isset($_SESSION['error'])) : ?>
                <div class="alert alert-danger text-center py-2">
                    <?= $_SESSION['error']; ?>
                </div>
            <?php unset($_SESSION['error']); endif; ?>

            <!-- FORM LOGIN -->
            <form action="proses/proses_login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" 
                            placeholder="Masukkan username" autofocus required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="d-flex">
                        <input type="password" name="password" id="password"
                            class="form-control" placeholder="Masukkan password" required>
                        <button type="button" onclick="togglePassword(this)"
                            class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <script>
                    function togglePassword(btn) {
                        const input = document.getElementById("password");
                        const icon = btn.querySelector("i");
                        if (input.type === "password") {
                            input.type = "text";
                            icon.classList.remove("bi-eye");
                            icon.classList.add("bi-eye-slash");
                        } else {
                            input.type = "password";
                            icon.classList.remove("bi-eye-slash");
                            icon.classList.add("bi-eye");
                        }
                    }
                </script>
                <button type="submit" name="login"
                        class="btn btn-primary w-100 fw-semibold"> Login
                </button>
            </form>

            <!-- FOOTER -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    © <?= date('Y') ?> Sistem Kasir
                </small>
            </div>

        </div>
    </div>
</div>

</body>
</html>