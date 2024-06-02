<?php
session_start();
include_once 'assets/database/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_ids'])) {
    $booking_ids = $_POST['booking_ids'];
    
    foreach ($booking_ids as $booking_id) {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE user_id = :booking_id");
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    // Redirect back to bookings_list.php or display a confirmation message
    header('Location: bookings_list');
    exit();
}
?>
