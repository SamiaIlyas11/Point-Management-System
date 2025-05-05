<?php
session_start();

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assign variables and sanitize input
    $studentName = htmlspecialchars($_POST['name']);
    $studentId = htmlspecialchars($_POST['student-id']);
    $pointnumber = htmlspecialchars($_POST['point-number']);

    // Basic validation (in real-world scenarios, more complex validation and security measures are required)
    if (empty($studentName) || empty($studentId) || empty($pointnumber)) {
        echo "Please fill in all fields correctly.";
    } else {
        // Print the data here; in a real scenario, you might also insert data into the database
        echo '<div id="challanToPrint" style="text-align: center; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%;">';
        echo "<h1>FAST UNIVERSITY</h1>";
        echo "<p><strong>Student Name:</strong> $studentName</p>";
        echo "<p><strong>Student ID:</strong> $studentId</p>";
        echo "<p><strong>Point Number:</strong> $pointnumber</p>";
        echo "<p><strong>Fee Amount:</strong> Rs 35000</p>";
        echo "<p><strong>Fee Challan generated successfully</strong></p>";
        echo '<button onclick="printChallan()">Print Challan</button>';
        echo '</div>';

        // Example of inserting data into a table (commented out)
        /*
        $stmt = $conn->prepare("INSERT INTO students (student_id, student_name, point_number) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $studentId, $studentName, $pointnumber);
        $stmt->execute();
        $stmt->close();
        */
    }
}

// Close connection
$conn->close();
?>

<script>
    // Function to trigger printing
    function printChallan() {
        var content = document.getElementById('challanToPrint').innerHTML;
        var originalContent = document.body.innerHTML;

        document.body.innerHTML = content; // Change the body's content to only the challan's content
        window.print(); // Call the browser's print function
        document.body.innerHTML = originalContent; // Restore the original content
    }
</script>