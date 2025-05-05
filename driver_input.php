<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = ""; 
    $db_name = 'point_management';

    $port = 3307;  

    // Create connection
    $conn = new mysqli($servername, $username, $password, $db_name, $port);

    // Check the database connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve and sanitize form data
    $driverName = $conn->real_escape_string($_POST['Name']);
    $driverRoute = $conn->real_escape_string($_POST['Route']);
    $pointNo = $conn->real_escape_string($_POST['Point_no']);
    $driverPhone = $conn->real_escape_string($_POST['Phone']);
    $driverID = $conn->real_escape_string($_POST['Driver_ID']);

    // Validate phone number: Ensure it contains exactly 10 digits
    if (!preg_match('/^\d{10}$/', $driverPhone)) {
        echo "Invalid phone number. Please enter a 10-digit phone number.";
    } else {
        // Check if a driver with the same ID already exists
        $checkStmt = $conn->prepare("SELECT * FROM driver WHERE Driver_ID = ?");
        $checkStmt->bind_param("s", $driverID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $checkStmt->close();

        if ($result->num_rows > 0) {
            echo "A driver with this ID already exists.";
        } else {
            // If no record exists, prepare the SQL statement to insert the driver's details
            $stmt = $conn->prepare("INSERT INTO driver (Name, Route, Point_no, Phone, Driver_ID) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssis", $driverName, $driverRoute, $pointNo, $driverPhone, $driverID);

            // Execute the statement and check if the insertion was successful
            if ($stmt->execute()) {
                echo "New driver added successfully";
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close the prepared statement
            $stmt->close();
        }
    }

    // Close the database connection
    $conn->close();
}
?>
