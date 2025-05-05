<?php
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "point_management";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $latitude = $_POST['lat'];
    $longitude = $_POST['lng'];
    // Process the coordinates
    echo "AJAX Received: Latitude = $latitude, Longitude = $longitude";
}
?>