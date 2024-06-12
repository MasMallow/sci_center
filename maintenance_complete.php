<?php
session_start();
require_once 'assets/database/dbConfig.php';
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_maintenance'])) {
    if (isset($_POST['id']) && is_array($_POST['id'])) {
        $ids = $_POST['id'];
        $sMessage = "แจ้งเตือนการบำรุงรักษา\n";

        foreach ($ids as $id) {
            $update_query = $conn->prepare("UPDATE crud SET availability = 0 WHERE id = :id");
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query->execute();

            $update_query = $conn->prepare("UPDATE crud SET last_maintenance_date = NOW() WHERE id = :id");
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query->execute();

            // Fetch item details for notification
            $stmt = $conn->prepare("SELECT * FROM crud WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            $namesci = $item['sci_name'];

            $update_query = $conn->prepare("
                UPDATE maintenance_history
                SET end_maintenance = NOW()
                WHERE sci_name = :namesci
                AND start_maintenance = (
                    SELECT MAX(start_maintenance)
                    FROM maintenance_history
                    WHERE sci_name = :namesci
                )
            ");
            $update_query->bindParam(':namesci', $namesci, PDO::PARAM_STR);
            $update_query->execute();

            if ($item) {
                $sMessage .= "รายการ: " . htmlspecialchars($item['sci_name'], ENT_QUOTES, 'UTF-8') . "\n";
                $sMessage .= "ประเภท: " . htmlspecialchars($item['categories'], ENT_QUOTES, 'UTF-8') . "\n";
            }
        }

        $sMessage .= "วันที่บำรุงรักษาสำเร็จ : " . date('d/m/Y H:i:s') . "\n";
        $sMessage .= "-------------------------------";

        $sToken = "7ijLerwP9wvrN0e3ykl8y3y9c991p1WQuX1Dy8Pv3Fx";

        // Line Notify settings
        $chOne = curl_init();
        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($chOne, CURLOPT_POST, 1);
        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken);
        curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($chOne);

        if (curl_error($chOne)) {
            echo 'error:' . curl_error($chOne);
        } else {
            $result_ = json_decode($result, true);
            if ($result_['status'] == 200) {
                echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'การบำรุงรักษาเสร็จสิ้น',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.href = 'home.php';
                        });
                      </script>";
            } else {
                echo "<script>alert('Notification failed: " . htmlspecialchars($result_['message'], ENT_QUOTES, 'UTF-8') . "');</script>";
            }
        }
        curl_close($chOne);

        header('Location: /project/maintenance?action=end_maintenance');
        exit;
    }
}
?>