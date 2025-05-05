<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "point_management";
$port = 3307;  

// Create connection
$conn = new mysqli($servername, $username, $password, $db_name, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['Driver_ID'])) {
    // Process the delete action
    $driverID = $conn->real_escape_string($_POST['Driver_ID']);
    $deleteSql = "DELETE FROM driver WHERE Driver_ID = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("s", $driverID);
    if ($deleteStmt->execute()) {
        echo json_encode(["success" => "Driver deleted successfully"]);
    } else {
        echo json_encode(["error" => "Error deleting driver: " . $deleteStmt->error]);
    }
    $deleteStmt->close();
} else {
    // Fetch and return all drivers' data
    $sql = "SELECT Driver_ID, Name, Route, Point_no, Phone FROM driver";
    $result = $conn->query($sql);
    $drivers = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $drivers[] = $row;
        }
        echo json_encode($drivers);
    } else {
        echo json_encode([]);
    }
}

$conn->close();
?>
