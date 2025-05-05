<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "point_managament";

// Create connection
$conn = new mysqli($servername, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT latitude, longitude FROM locations ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'No data found']);
}

$conn->close();
?>