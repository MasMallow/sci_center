<?php
// ฟังก์ชันแปลงวันที่และเวลาเป็นรูปแบบภาษาไทย
function thai_date_time($datetime)
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

    return "วันที่ $date {$thai_month_arr[$month]} พ.ศ. $year <br> เวลา $time";
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
function thai_date_time_3($datetime)
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

    return "วันที่ $date {$thai_month_arr[$month]} พ.ศ. $year";
}
function thai_date_time_4($datetime)
{
    $thai_month_arr = array(
        1 => "มกราคม",
        2 => "กุมภาพันธ์",
        3 => "มีนาคม",
        4 => "เมษายน",
        5 => "พฤษภาคม",
        6 => "มิถุนายน",
        7 => "กรกฎาคม",
        8 => "สิงหาคม",
        9 => "กันยายน",
        10 => "ตุลาคม",
        11 => "พฤศจิกายน",
        12 => "ธันวาคม"
    );

    $dt = new DateTime($datetime);
    $date = $dt->format('j'); // วันที่
    $month = (int)$dt->format('n'); // เดือน (1-12)
    $year = $dt->format('Y') + 543; // ปี พ.ศ.

    return "วันที่ $date {$thai_month_arr[$month]} พ.ศ. $year";
}
function thai_date($datetime)
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

    return "วันที่ $date {$thai_month_arr[$month]} พ.ศ. $year";
}
function thai_time($datetime)
{
    $dt = new DateTime($datetime);
    return $dt->format('เวลา H:i น.'); // เวลา
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
