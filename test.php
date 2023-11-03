<!DOCTYPE html>
<html>
<head>
    <title>หน้าเว็บ</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
<body>
    <!-- เนื้อหาหน้าเว็บ -->

    <script>
        // รหัส JavaScript สำหรับ SweetAlert2
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: 'การยืมเสร็จสิ้น',
            showConfirmButton: false,
            timer: 1500
        }).then(function() {
            window.location.href = 'ajax.php';
        });
    </script>
</body>
</html>
