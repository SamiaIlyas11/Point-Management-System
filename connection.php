<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$db_name = 'point_management';

$port = 3307;  

// Create connection
$conn = new mysqli($servername, $username, $password, $db_name, $port);

// Check connection
if ($conn->connect_error) {
    die("Cannot connect to Database: " . $conn->connect_error);
} else {
    echo "Connected successfully";
}
?>
