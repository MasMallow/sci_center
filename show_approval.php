<?php
session_start();
include_once 'assets/database/connect.php';
date_default_timezone_set('Asia/Bangkok');

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] !== 'approved') {
            header("Location: home.php");
            exit();
        }
    }
} else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}
$firstname = $userData['surname'];
$stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE firstname = :firstname  ORDER BY id ");
$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>UDI</th>
                <th>Serial Number</th>
                <th>Firstname</th>
                <th>Item Borrowed</th>
                <th>Borrow DateTime</th>
                <th>Return Date</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['udi']); ?></td>
                    <td><?php echo htmlspecialchars($row['sn']); ?></td>
                    <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                    <td>
                        <?php
                        // แยกข้อมูล Item Borrowed
                        $items = explode(',', $row['itemborrowed']);
                        // แสดงข้อมูลรายการที่ยืม
                        foreach ($items as $item) {
                            $item_parts = explode('(', $item); // แยกชื่อสินค้าและจำนวนชิ้น
                            $product_name = trim($item_parts[0]); // ชื่อสินค้า (ตัดวงเล็บออก)
                            $quantity = str_replace(')', '', $item_parts[1]); // จำนวนชิ้น (ตัดวงเล็บออกและตัดช่องว่างข้างหน้าและหลัง)
                            echo $product_name . ' <span id="B"> ' . $quantity . ' ชิ้น </span><br>';
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['borrowdatetime']); ?></td>
                    <td><?php echo htmlspecialchars($row['returndate']); ?></td>
                    <td>
                        <?php
                        echo $row['situation'] === null ? 'ยังไม่ได้รับอนุมัติ' : ($row['situation'] == 1 ? 'ได้รับอนุมัติ' : htmlspecialchars($row['situation']));
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>