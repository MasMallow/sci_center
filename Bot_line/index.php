<?php
require_once 'dbConfig.php';
$accessToken = 'vnFizyKjnCrDU6UzKaBozQiRA2Dewe1B7Zi8nNTlebY3spBHgNoEdEh69i6eabjgcs/Tuf7TJa3mNiLqF5EdVaghNq9BXpT3WKvIoaCnnHCr92nRHB/U3En4etKdFoLh6cfd3QHDLUzQP544L754RwdB04t89/1O/w1cDnyilFU=';
$channelSecret = '1b03b88554456c8e8cd58610e4406300';

$input = file_get_contents('php://input');
$hash = hash_hmac('sha256', $input, $channelSecret, true);
$signature = base64_encode($hash);
// include_once 'assets/includes/thai_date_time.php';

function connectDatabase()
{
    // Database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "science_center_management";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn; // Return PDO connection object
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        return null; // Return null on connection failure
    }
}

if ($_SERVER['HTTP_X_LINE_SIGNATURE'] !== $signature) {
    die('Invalid request signature');
}

$data = json_decode($input, true);
foreach ($data['events'] as $event) {
    if ($event['type'] === 'message' && $event['message']['type'] === 'text') {
        $userMessage = $event['message']['text'];
        $replyToken = $event['replyToken'];

        $replyMessage = 'ขอโทษครับ ผมไม่เข้าใจคำถามของคุณ';
        $replyMessage .= "\n\nโปรดถามดังต่อไปนี้ เช่น จำนวนคงเหลือ หรือ ถามตามหมายเลข serial number หรือ บำรุงรักษา";

        if (strpos($userMessage, 'จำนวนคงเหลือ') !== false || strpos($userMessage, 'คงเหลือ') !== false) {
            $replyMessage = getAvailability();
        } elseif (preg_match('/^[a-zA-Z0-9]{7}$/', $userMessage)) {
            $replyMessage = getAvailabilityBySerialNumber($userMessage);
        } elseif (strpos($userMessage, 'บำรุงรักษา') !== false) {
            $replyMessage = getUnderMaintenanceItems(); // Call the new function for maintenance items
        } else {
            $replyMessage = getBorrowingHistory($userMessage);
        }

        replyMessage($accessToken, $replyToken, $replyMessage);
    }
}

function replyMessage($accessToken, $replyToken, $message)
{
    $url = 'https://api.line.me/v2/bot/message/reply';
    $data = [
        'replyToken' => $replyToken,
        'messages' => [['type' => 'text', 'text' => $message]]
    ];
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\nAuthorization: Bearer $accessToken\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
        ],
    ];
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        $error = error_get_last();
        file_put_contents('error_log.txt', print_r($error, true), FILE_APPEND);
    } else {
        file_put_contents('response_log.txt', $result, FILE_APPEND);
    }
}

function getAvailability()
{
    $conn = connectDatabase(); // Connect to database

    if (!$conn) {
        return "ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้";
    }

    $sql = "SELECT sci_name, amount FROM crud";
    $stmt = $conn->query($sql);

    if ($stmt->rowCount() > 0) {
        $replyMessage = "จำนวนคงเหลือของอุปกรณ์:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $replyMessage .= $row["sci_name"] . ": " . $row["amount"] . " ชิ้น\n";
        }
    } else {
        $replyMessage = "ไม่พบข้อมูลการยืม";
    }

    $conn = null; // Close the database connection
    return $replyMessage;
}

function getAvailabilityBySerialNumber($serialNumber)
{
    $conn = connectDatabase(); // Connect to database

    if (!$conn) {
        return "ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้";
    }

    $sql = "SELECT * FROM approve_to_reserve WHERE serial_number = :serial_number";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':serial_number', $serialNumber);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $replyMessage = "ข้อมูลการยืมของ $serialNumber:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $replyMessage .= "รหัสการยืม: " . $row["serial_number"] . "\n";
            $replyMessage .= "ชื่อผู้ยืม: " . $row["name_user"] . "\n";

            $listItems = explode(',', $row['list_name']);
            foreach ($listItems as $item) {
                $item = trim($item); // Remove any leading/trailing whitespace
                if ($item == '') continue; // Skip empty parts
                list($product_name, $quantity) = explode('(', $item);
                $product_name = trim($product_name);
                $quantity = str_replace(')', '', trim($quantity));
                $replyMessage .= "ชื่อรายการ: " . $product_name . " จำนวน " . $quantity . " ชิ้น\n";
            }

            $replyMessage .= "วันที่ขอใช้: " . thai_date_time_2($row["reservation_date"]) . "\n";
            $replyMessage .= "วันสิ้นสุดการขอใช้: " . $row["end_date"] . "\n";
            $replyMessage .= "วันที่ทำรายการ: " . $row["created_at"] . "\n";
            $replyMessage .= "ผู้อนุมัติ: " . $row["approver"] . "\n";
            $replyMessage .= "สถานะ: " . ($row["situation"] == 1 ? "อนุมัติ" : "รออนุมัติ") . "\n";
            $replyMessage .= "\n";
        }
    } else {
        $replyMessage = "ไม่พบข้อมูลการยืมสำหรับ serial number นี้";
    }

    $conn = null; // Close the database connection
    return $replyMessage;
}

function getBorrowingHistory($username)
{
    $conn = connectDatabase(); // Connect to database

    if (!$conn) {
        return "ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้";
    }

    $sql = "SELECT * FROM approve_to_reserve WHERE name_user LIKE :username";
    $stmt = $conn->prepare($sql);
    $likeUsername = "%" . $username . "%";
    $stmt->bindParam(':username', $likeUsername);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $replyMessage = "ประวัติการยืมของ $username:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $replyMessage .= "ID: " . $row["user_id"] . "\n";
            $replyMessage .= "รหัสการยืม: " . $row["serial_number"] . "\n";
            $replyMessage .= "ชื่อผู้ยืม: " . $row["name_user"] . "\n";

            $listItems = explode(',', $row['list_name']);
            foreach ($listItems as $item) {
                $item = trim($item); // Remove any leading/trailing whitespace
                if ($item == '') continue; // Skip empty parts
                list($product_name, $quantity) = explode('(', $item);
                $product_name = trim($product_name);
                $quantity = str_replace(')', '', trim($quantity));
                $replyMessage .= "ชื่อรายการ: " . $product_name . " จำนวน " . $quantity . " ชิ้น\n";
            }

            $replyMessage .= "วันที่ขอใช้: " . $row["reservation_date"] . "\n";
            $replyMessage .= "วันสิ้นสุดการขอใช้: " . $row["end_date"] . "\n";
            $replyMessage .= "วันที่ทำรายการ: " . $row["created_at"] . "\n";
            $replyMessage .= "ผู้อนุมัติ: " . $row["approver"] . "\n";
            $replyMessage .= "สถานะ: " . ($row["situation"] == 1 ? "อนุมัติ" : "รออนุมัติ") . "\n";
            $replyMessage .= "\n";
        }
    } else {
        $replyMessage = "ไม่พบข้อมูลการยืมสำหรับชื่อผู้ใช้นี้";
    }

    $conn = null; // Close the database connection
    return $replyMessage;
}

function getUnderMaintenanceItems()
{
    $conn = connectDatabase(); // Connect to database

    if (!$conn) {
        return "ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้";
    }

    $sql = "SELECT sci_name, amount FROM crud WHERE availability = 1"; // Filter by availability = 1
    $stmt = $conn->query($sql);

    if ($stmt->rowCount() > 0) {
        $replyMessage = "อุปกรณ์ที่กำลังทำการบำรุงรักษา:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $replyMessage .= $row["sci_name"] . ": " . $row["amount"] . " ชิ้น\n";
        }
    } else {
        $replyMessage = "ไม่พบข้อมูลอุปกรณ์ที่กำลังทำการบำรุงรักษา";
    }

    $conn = null; // Close the database connection
    return $replyMessage;
}

function thai_date_time_2($datetime)
{
    $thai_month_arr = array(
        1 => "ม.ค.",
        2 => "ก.พ.",
        3 => "มี.ค.",
        4 => "เม.ย.",
        5 => "พ.ค.",
        6 => "มิ.ย.",
        7 => "ก.ค.",
        8 => "ส.ค.",
        9 => "ก.ย.",
        10 => "ต.ค.",
        11 => "พ.ย.",
        12 => "ธ.ค."
    );

    $dt = new DateTime($datetime);
    $date = $dt->format('j'); // วันที่
    $month = (int)$dt->format('n'); // เดือน (1-12)
    $year = $dt->format('Y') + 543; // ปี พ.ศ.
    $time = $dt->format('H:i น.'); // เวลา

    return "วันที่ $date {$thai_month_arr[$month]} พ.ศ. $year  เวลา $time";
}