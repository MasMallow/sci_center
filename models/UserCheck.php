<?php
if (isset($_SESSION['user_login'])) {
    $userID = $_SESSION['user_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ตรวจสอบว่าผู้ใช้หรือพนักงานเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['staff_login']) && !isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

$userID = null;
$userData = null;
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
} elseif (isset($_SESSION['user_login'])) {
    $userID = $_SESSION['user_login'];
}

$stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userData && isset($_SESSION['user_login'])) {
    if ($userData['status'] == 'n_approved') {
        unset($_SESSION['user_login']);
        header('Location: /sign_in');
        exit();
    } elseif ($userData['status'] == 'w_approved') {
        unset($_SESSION['reserve_cart']);
        header('Location: /');
        exit();
    }
}
