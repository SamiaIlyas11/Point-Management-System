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
} //else {
    //echo "Connected successfully";
//}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the input values
    $email = $_POST['input-email'];
    $password = $_POST['input-pass'];

    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT email, admin_password FROM admin_login WHERE email = ? AND admin_password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Authentication successful
        $row = $result->fetch_assoc();

        // Store user details in session variables
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_email'] = $row['email'];
        echo "Login successful! Redirecting...";
        sleep(2);
        // Redirect to the index.php or any other desired page
        header("location: admin.html");
        exit(); // Ensure script execution stops after redirection
    } else {
        // Invalid credentials
        echo "Invalid email or password";
        header("Location: admin_login.html?error=1");
    }

    // Close the prepared statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>