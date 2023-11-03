<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['firstname']) && isset($_POST['items'])) {
        // เปลี่ยน $firstname, $items, และ $returnDates เป็นข้อมูลที่ถูกส่งมา
        $firstname = $_POST['firstname'];
        $items = $_POST['items'];
        $returnDates = $_POST['return_dates'];

        $sToken = "UiLcDHZULFEO0bv4qvB7QYE8b1jXyvCrlynhcXeNnQg";
        $sMessage = "มีรายการยืมของแล้ว\n";
        $sMessage .= "ชื่อไอดี: " . $firstname . "\n";
        $sMessage .= "ของที่ยืม: " . $items . "\n";
        $sMessage .= "วันที่คืน: " . $returnDates . "\n";

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

        //Result error 
        if (curl_error($chOne)) {
            echo 'error:' . curl_error($chOne);
        } else {
            $result_ = json_decode($result, true);
            echo "status : " . $result_['status'];
            echo "message : " . $result_['message'];
        }
        curl_close($chOne);
    } else {
        echo 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }
}
?>