<?php
session_start();
include_once 'assets/database/connect.php';
if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (isset($_SESSION['admin_login'])) {
    $user_id = $_SESSION['admin_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
$stmt = $conn->prepare("SELECT product_name, quantity, borrow_date, return_date FROM borrow_history WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$borrowHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($borrowHistory)) {
    $reportHTML = '<div class="alert alert-info">ไม่พบรายการการยืมสินค้าสำหรับไอดีนี้</div>';
} else {
    $reportHTML = '<div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>สินค้า</th>
                                    <th>จำนวน</th>
                                    <th>วันที่ยืม</th>
                                    <th>วันที่คืน</th>
                                    <th><a href="#" onclick="Return()">คืนอุปกรณ์</a></th>
                                </tr>
                            </thead>
                        <tbody>';
    foreach ($borrowHistory as $item) {
        $reportHTML .= '<tr>
                            <td>' . $item['product_name'] . '</td>
                            <td>' . $item['quantity'] . '</td>
                            <td>' . $item['borrow_date'] . '</td>
                            <td>' . $item['return_date'] . '</td>
                        </tr>';
    }
    $reportHTML .= '</tbody>
                    </table>
                </div>';
}

echo $reportHTML;
?>
