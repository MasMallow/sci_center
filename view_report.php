<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการยืมสินค้า</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">รายงานการยืมสินค้า</h1>

        <!-- Form to enter user ID -->
        <form action="report.php" method="GET">
            <div class="form-group">
                <label for="userID">กรุณาใส่ไอดีผู้ใช้:</label>
                <input type="text" class="form-control" id="userID" name="user_id" placeholder="กรอกไอดีผู้ใช้">
            </div>
            <button type="submit" class="btn btn-primary">ดูรายงาน</button>
        </form>

        <!-- Display report result here -->
        <div id="reportResult" class="mt-4"></div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
