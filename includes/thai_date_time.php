<?php
// ฟังก์ชันแปลงวันที่และเวลาเป็นรูปแบบภาษาไทย
function thai_date_time($datetime)
{
    $thai_month_arr = array(
        "0" => "",
        "1" => "ม.ค.",
        "2" => "ก.พ.",
        "3" => "มี.ค.",
        "4" => "เม.ย.",
        "5" => "พ.ค.",
        "6" => "มิ.ย.",
        "7" => "ก.ค.",
        "8" => "ส.ค.",
        "9" => "ก.ย.",
        "10" => "ต.ค.",
        "11" => "พ.ย.",
        "12" => "ธ.ค."
    );

    $day = date("w", strtotime($datetime)); // วันในสัปดาห์ (0-6)
    $date = date("j", strtotime($datetime)); // วันที่
    $month = date("n", strtotime($datetime)); // เดือน (1-12)
    $year = date("Y", strtotime($datetime)) + 543; // ปี พ.ศ.
    $time = date("H:i น.", strtotime($datetime)); // เวลา

    return "วัน" . "ที่ " . $date . " " . $thai_month_arr[$month] . " พ.ศ." . $year . " <br> เวลา " . $time;
}
function format_phone_number($phone_number)
{
    // Remove any non-digit characters
    $phone_number = preg_replace('/[^0-9]/', '', $phone_number);

    // Format the phone number
    if (strlen($phone_number) == 10) {
        return substr($phone_number, 0, 3) . '-' . substr($phone_number, 3, 3) . '-' . substr($phone_number, 6);
    } else {
        // Return the original phone number if it's not 10 digits
        return $phone_number;
    }
}
