<?php
session_start();
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch borrow history for the specified user_id
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
} else {
    echo '<div class="alert alert-danger">โปรดระบุไอดีผู้ใช้ให้ถูกต้อง</div>';
}
?>
