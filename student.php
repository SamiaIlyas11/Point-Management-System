<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "point_management";

$port = 3307;  

// Create connection
$conn = new mysqli($servername, $username, $password, $db_name, $port);


// Check the database connection
if ($conn->connect_error) {
    die("Cannot connect to Database: " . $conn->connect_error);
} else {
    echo "Connected successfully";
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the input values
    $id = $conn->real_escape_string($_POST['input-id']);
    $password = $conn->real_escape_string($_POST['input-pass']);

    // Validate the Student_ID format (only if you still need this validation)
    if (!preg_match('/^K\d{6}$/', $id)) {
        echo "Invalid Student ID format. It should start with 'K' followed by 6 digits.";
        exit(); // Stop script execution if the ID format is incorrect
    }

    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM student WHERE Student_ID = ? AND student_password = ?");

    // Check for errors in preparing the statement
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $bindResult = $stmt->bind_param("ss", $id, $password);

    // Check if binding parameters was successful
    if ($bindResult === false) {
        die("Error binding parameters: " . $stmt->error);
    }

    // Execute the statement
    $executeResult = $stmt->execute();

    // Check if execution was successful
    if ($executeResult === false) {
        die("Error executing statement: " . $stmt->error);
    }

    // Get result set
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Authentication successful
        $row = $result->fetch_assoc();

        // Store user details in session variables
        $_SESSION['Student_ID'] = $row['Student_ID'];
        $_SESSION['Name'] = $row['Name'];
        $_SESSION['Point_no'] = $row['Point_no'];
        $_SESSION['Phone'] = $row['Phone'];
        $_SESSION['Fee_Status'] = $row['Fee_Status'];
        $_SESSION['Driver_ID'] = $row['Driver_ID'];

        // Redirect to the student.html or any other desired page
        header("location: student.html");
        exit(); // Ensure script execution stops after redirection
    } else {
        // Invalid credentials
        echo "Invalid Student ID or password";
    }

    // Close the prepared statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>