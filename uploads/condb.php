<?php
$servername = "localhost";
$username = "root";
$password = "";
$connname = "testshop";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $connname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// echo "Connected successfully";
?>
<?php
$connHost = "localhost";
$username = "root";
$password = "";
$connname = "upload_image";

$conn = new mysqli($connHost,$username,$password,$connname);
if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}
// echo "Connected successfully";
?>