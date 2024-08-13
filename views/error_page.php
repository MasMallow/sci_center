<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Page</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">

    <style>
        body {
            background-color: #f2f2f2;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #333;
        }

        .error-container {
            text-align: center;
            max-width: 500px;
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        }

        .error-container img {
            width: 120px;
            margin-bottom: 20px;
        }

        .error-container h1 {
            font-size: 48px;
            margin: 0 0 10px;
            color: #ff6b6b;
        }

        .error-container p {
            font-size: 18px;
            margin: 10px 0;
        }

        .error-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .error-container a:hover {
            background-color: #0056b3;
        }

        .error-container .icon {
            font-size: 80px;
            color: #ff6b6b;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <i class="fas fa-exclamation-triangle icon"></i>
        <img src="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" alt="Logo">
        <h1>เกิดข้อผิดพลาด</h1>
        <p>ขออภัยเกิดข้อผิดพลาดในการประมวลผลคำขอของคุณ</p>
        <p>โปรดลองอีกครั้งในภายหลังหรือติดต่อผู้ดูแลระบบ</p>
        <a href="/">หน้าหลักเว็บไซต์</a>
    </div>

    <!-- -------------- FOOTER -------------- -->
    <footer>
        <?php include_once 'assets/includes/footer.php'; ?>
    </footer>
</body>

</html>