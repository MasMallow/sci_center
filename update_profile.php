<?php
session_start();
require_once 'assets/database/connect.php';

// Check if the user is logged in as either a regular user or an admin
if (isset($_SESSION['user_login']) || isset($_SESSION['admin_login'])) {
    // Determine the user ID based on the session variable
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['admin_login'];

    // Sanitize and validate user inputs
    $firstname = isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : '';
    $lastname = isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : '';
    $urole = isset($_POST['urole']) ? htmlspecialchars($_POST['urole']) : '';
    $newUsername = isset($_POST['newUsername']) ? htmlspecialchars($_POST['newUsername']) : '';
    $newPassword = isset($_POST['newPassword']) ? htmlspecialchars($_POST['newPassword']) : '';

    // Prepare a password update query if a new password is provided
    $passwordUpdateQuery = '';
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $passwordUpdateQuery = ", password='$hashedPassword'";
    }

    // Update the user information in the database
    $sql = "UPDATE users 
            SET firstname='$firstname', lastname='$lastname', urole='$urole', username='$newUsername' $passwordUpdateQuery 
            WHERE user_id=$user_id";

    // Execute the SQL query
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->$error;
    }

    // Close the database connection
    $conn = null;
} else {
    // If the user is not logged in, display an error message
    echo "You are not logged in!";
}
?>
