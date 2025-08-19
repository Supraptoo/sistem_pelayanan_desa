<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            title: 'Logout Berhasil!',
            text: 'Anda akan dialihkan ke halaman login',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        }).then(function() {
            window.location.href = "login.php";
        });
    </script>
</body>
</html>