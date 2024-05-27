    <?php
    session_start();
    include_once 'assets/database/connect.php';
    date_default_timezone_set('Asia/Bangkok');

    if (!isset($_SESSION['staff_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('Location: auth/sign_in.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        $selectedIds = $_POST['selected_ids'];
        $end_date =$_POST['end_date'];
        if (isset($_POST['note'])) {
            $note = $_POST['note'];
        } else {
            $note = '';
        }
        $sMessage = "แจ้งเตือนการบำรุงรักษา\n";
        foreach ($selectedIds as $id) {
            $update_query = $conn->prepare("UPDATE crud SET Availability = 1 WHERE id = :id");
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query->execute();

            // Assuming you want to add details of each item to the message
            $stmt = $conn->prepare("SELECT * FROM crud WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                $sMessage .= "รายการ: " . $item['sci_name'] . "\n";
                $sMessage .= "ประเภท: " . $item['categories'] . "\n";
            }
        }
        $sMessage .= "วันที่บำรุงรักษา : " . date('d/m/Y') . "\n";
        $sMessage .= "บำรุงรักษาสำเร็จ : " . $end_date . "\n";
        $sMessage .=  "หมายเหตุ: " . $note . "\n";
        $sMessage .= "-------------------------------";

        $sToken = "7ijLerwP9wvrN0e3ykl8y3y9c991p1WQuX1Dy8Pv3Fx";

        // Line Notify settings
        $chOne = curl_init();
        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($chOne, CURLOPT_POST, 1);
        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
        curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($chOne);

        if (curl_error($chOne)) {
            echo 'error:' . curl_error($chOne);
        } else {
            $result_ = json_decode($result, true);
            echo "<script>
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'การยืมเสร็จสิ้น',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = 'home.php';
                });
                </script>";
        }
        curl_close($chOne);
        header('Location: /project/maintenance.php');
        exit;
    } 