<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = 'point_management';

    $port = 3307;  

    // Create connection
    $conn = new mysqli($servername, $username, $password, $db_name, $port);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve and sanitize form data
    $studentID = $conn->real_escape_string($_POST['Student_ID']);
    $studentName = $conn->real_escape_string($_POST['Name']);
    $pointnumber = $conn->real_escape_string($_POST['Point_no']);
    $contactnumber = $conn->real_escape_string($_POST['Phone']);
    $feestatus = $conn->real_escape_string($_POST['Fee_Status']);
    $driverid = $conn->real_escape_string($_POST['Driver_ID']);

    // Check if the student ID already exists
    $checkQuery = "SELECT Student_ID FROM student WHERE Student_ID = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $studentID);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        echo "Error: Student ID already exists.";
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO student (Student_ID, Name, Point_no, Phone, Fee_Status, Driver_ID) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $studentID, $studentName, $pointnumber, $contactnumber, $feestatus, $driverid);

        // Execute and check for success
        if ($stmt->execute()) {
            echo "New student added successfully";
            // Optionally, redirect back to a confirmation page or the student list
            // header('Location: student_list.php');
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();
}
?>
