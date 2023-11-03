<?php
$servername = "localhost";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=register_system", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>


<?php
$dbHost = "localhost";
$username = "root";
$password = "";
$dbname = "register_system";

$db = new mysqli($dbHost,$username,$password,$dbname);
if ($db->connect_error) {
    die("Connection error: " . $db->connect_error);
}
?>