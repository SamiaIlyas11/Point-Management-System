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
} // else {
// echo "Connected successfully";
//}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the input values
    $id = $_POST['input-id'];
    $password = $_POST['input-pass'];

    // Validate the Student_ID format
    if (!preg_match('/^K\d{6}$/', $id)) {
        echo "Invalid Student ID format. It should start with 'K' followed by 6 digits.";
        exit(); // Stop script execution if the ID format is incorrect
    }

    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT Student_ID, student_password FROM student_login WHERE Student_ID = ? AND student_password = ?");
    $stmt->bind_param("ss", $id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Authentication successful
        $row = $result->fetch_assoc();

        // Store user details in session variables
        $_SESSION['user_id'] = $row['Student_ID'];
        $_SESSION['user_email'] = $id; // Assuming you want to store the ID as 'user_email'
        echo "Login successful! Redirecting...";
        sleep(2);
        // Redirect to the student.html or any other desired page
        header("location: student.html");
        exit(); // Ensure script execution stops after redirection
    } else {
        // Invalid credentials
        echo "Invalid email or password. Login unsuccessful! Redirecting...";
        header("Location: student_login.html?error=1");
    }

    // Close the prepared statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>