<?php
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['Student_ID'])) {
    // Process the delete action for a student
    $studentID = $conn->real_escape_string($_POST['Student_ID']);
    $deleteSql = "DELETE FROM student WHERE Student_ID = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("s", $studentID);
    if ($deleteStmt->execute()) {
        echo json_encode(["success" => "Student deleted successfully"]);
    } else {
        echo json_encode(["error" => "Error deleting student: " . $deleteStmt->error]);
    }
    $deleteStmt->close();
} else {
    // Fetch and return all students' data
    $sql = "SELECT Student_ID, Name, Point_no, Phone, Fee_Status , Driver_ID FROM student";
    $result = $conn->query($sql);
    $students = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        echo json_encode($students);
    } else {
        echo json_encode([]);
    }
}
?>
