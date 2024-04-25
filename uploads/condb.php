<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testshop";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// echo "Connected successfully";
?>
<?php
$dbHost = "localhost";
$username = "root";
$password = "";
$dbname = "upload_image";

$db = new mysqli($dbHost,$username,$password,$dbname);
if ($db->connect_error) {
    die("Connection error: " . $db->connect_error);
}
// echo "Connected successfully";
?>