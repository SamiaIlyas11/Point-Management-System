<?php
use PHPUnit\Framework\TestCase;

class DriverDataTest extends TestCase
{
    /**
     * Test database connection
     */
    public function testDatabaseConnection()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "point_management";
        
        // Act
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Assert
        $this->assertNull($conn->connect_error, "Database connection failed: " . $conn->connect_error);
        
        // Clean up
        $conn->close();
    }

    /**
     * Test fetching driver data when drivers exist
     */
    public function testFetchDriverDataWhenDriversExist()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "point_management";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $this->assertNull($conn->connect_error, "Database connection failed");
        
        $sql = "SELECT Driver_ID, Name, Route, Point_no, Phone FROM driver";
        
        // Act
        $result = $conn->query($sql);
        
        // Assert
        $this->assertNotFalse($result, "Query failed: " . $conn->error);
        
        // If there are drivers, check the result
        if ($result->num_rows > 0) {
            $drivers = [];
            while ($row = $result->fetch_assoc()) {
                $drivers[] = $row;
            }
            
            $this->assertGreaterThan(0, count($drivers), "No drivers found in the database");
            
            // Optional: Check structure of the first driver
            $firstDriver = $drivers[0];
            $this->assertArrayHasKey('Driver_ID', $firstDriver);
            $this->assertArrayHasKey('Name', $firstDriver);
            $this->assertArrayHasKey('Route', $firstDriver);
            $this->assertArrayHasKey('Point_no', $firstDriver);
            $this->assertArrayHasKey('Phone', $firstDriver);
        }
        
        // Clean up
        $result->free();
        $conn->close();
    }

    /**
     * Test deleting a driver successfully
     */
    public function testDeleteDriverSuccessfully()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "point_management";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $this->assertNull($conn->connect_error, "Database connection failed");
        
        // Choose a driver ID that exists and is safe to delete
        $driverID = 'D003'; // Replace with an actual driver ID from your database
        
        // Prepare the delete statement
        $stmt = $conn->prepare("DELETE FROM driver WHERE Driver_ID = ?");
        $this->assertNotFalse($stmt, "Failed to prepare delete statement: " . $conn->error);
        
        // Act
        $stmt->bind_param("s", $driverID);
        $deleteResult = $stmt->execute();
        
        // Assert
        $this->assertTrue($deleteResult, "Failed to delete driver: " . $stmt->error);
        
        // Verify deletion
        $checkStmt = $conn->prepare("SELECT * FROM driver WHERE Driver_ID = ?");
        $checkStmt->bind_param("s", $driverID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        $this->assertEquals(0, $checkResult->num_rows, "Driver was not deleted");
        
        // Clean up
        $stmt->close();
        $checkStmt->close();
        $conn->close();
    }

    /**
     * Test attempting to delete a non-existent driver
     */
    public function testDeleteNonExistentDriver()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "point_management";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $this->assertNull($conn->connect_error, "Database connection failed");
        
        // Use a driver ID that definitely does not exist
        $nonExistentDriverID = 'NONEXISTENT999';
        
        // Prepare the delete statement
        $stmt = $conn->prepare("DELETE FROM driver WHERE Driver_ID = ?");
        $this->assertNotFalse($stmt, "Failed to prepare delete statement: " . $conn->error);
        
        // Act
        $stmt->bind_param("s", $nonExistentDriverID);
        $deleteResult = $stmt->execute();
        
        // Assert
        $this->assertTrue($deleteResult, "Delete operation failed");
        $this->assertEquals(0, $stmt->affected_rows, "Deleted a non-existent driver");
        
        // Clean up
        $stmt->close();
        $conn->close();
    }

    /**
     * Test listing drivers with pending student assignments
     */
    public function testListDriversWithPendingStudentAssignments()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "point_management";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $this->assertNull($conn->connect_error, "Database connection failed");
        
        // Act - Query to get drivers with no or few assigned students
        $sql = "SELECT d.Driver_ID, d.Name, d.Route, COUNT(s.Student_ID) as StudentCount 
                FROM driver d
                LEFT JOIN student s ON d.Driver_ID = s.Driver_ID
                GROUP BY d.Driver_ID, d.Name, d.Route
                ORDER BY StudentCount ASC";
        
        $result = $conn->query($sql);
        
        // Assert
        $this->assertNotFalse($result, "Query failed: " . $conn->error);
        $this->assertGreaterThan(0, $result->num_rows, "No drivers found in the database");
        
        // Fetch the results
        $drivers = [];
        while ($row = $result->fetch_assoc()) {
            $drivers[] = $row;
        }
        
        // Verify result structure
        $firstDriver = $drivers[0];
        $this->assertArrayHasKey('Driver_ID', $firstDriver);
        $this->assertArrayHasKey('Name', $firstDriver);
        $this->assertArrayHasKey('Route', $firstDriver);
        $this->assertArrayHasKey('StudentCount', $firstDriver);
        
        // Clean up
        $result->free();
        $conn->close();
    }
    /**
     * Test attempting to delete a driver with related students
     */
    public function testDeleteDriverWithRelatedStudents()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "point_management";
        
        // Establish database connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        $this->assertNull($conn->connect_error, "Database connection failed");
        
        // Prepare test data  
        $driverID = 'D001'; // Known driver with related students
        
        // Validate test precondition: driver exists
        $driverStmt = $conn->prepare("SELECT * FROM driver WHERE Driver_ID = ?");
        $driverStmt->bind_param("s", $driverID);
        $driverStmt->execute();
        $driverResult = $driverStmt->get_result();
        $this->assertEquals(1, $driverResult->num_rows, "Driver does not exist, test cannot proceed");

        // Validate test precondition: driver has related students  
        $checkStmt = $conn->prepare("SELECT COUNT(*) as student_count FROM student WHERE Driver_ID = ?");
        $checkStmt->bind_param("s", $driverID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $studentCount = $checkResult->fetch_assoc()['student_count'];
        $this->assertGreaterThan(0, $studentCount, "Driver has no related students, test cannot proceed");
        
        // Prepare delete statement
        $deleteStmt = $conn->prepare("DELETE FROM driver WHERE Driver_ID = ?");
        
        // Act
        $deleteStmt->bind_param("s", $driverID);
        $deleteSuccess = $deleteStmt->execute();
        
        // Assert
        $this->assertFalse($deleteSuccess, "Deleting driver with related students should fail");
        $this->assertEquals(1451, $deleteStmt->errno, "Expected a foreign key constraint error (1451)");
        
        // Clean up
        $driverStmt->close();
        $checkStmt->close();  
        $deleteStmt->close();
        $conn->close();
    }
}