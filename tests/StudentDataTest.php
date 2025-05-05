<?php
use PHPUnit\Framework\TestCase;

class StudentDataTest extends TestCase
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
     * Test fetching student data when students exist
     */
    public function testFetchStudentDataWhenStudentsExist()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "point_management";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $this->assertNull($conn->connect_error, "Database connection failed");
        
        $sql = "SELECT Student_ID, Name, Point_no, Phone, Fee_Status, Driver_ID FROM student";
        
        // Act
        $result = $conn->query($sql);
        
        // Assert
        $this->assertNotFalse($result, "Query failed: " . $conn->error);
        
        // If there are students, check the result
        if ($result->num_rows > 0) {
            $students = [];
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
            
            $this->assertGreaterThan(0, count($students), "No students found in the database");
            
            // Optional: Check structure of the first student
            $firstStudent = $students[0];
            $this->assertArrayHasKey('Student_ID', $firstStudent);
            $this->assertArrayHasKey('Name', $firstStudent);
            $this->assertArrayHasKey('Point_no', $firstStudent);
            $this->assertArrayHasKey('Phone', $firstStudent);
            $this->assertArrayHasKey('Fee_Status', $firstStudent);
            $this->assertArrayHasKey('Driver_ID', $firstStudent);
        }
        
        // Clean up
        $result->free();
        $conn->close();
    }

    /**
     * Test deleting a student successfully
     */
    public function testDeleteStudentSuccessfully()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "point_management";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $this->assertNull($conn->connect_error, "Database connection failed");
        
        // Choose a student ID that exists and is safe to delete
        $studentID = 'K213199'; // Replace with an actual student ID from your database
        
        // Prepare the delete statement
        $stmt = $conn->prepare("DELETE FROM student WHERE Student_ID = ?");
        $this->assertNotFalse($stmt, "Failed to prepare delete statement: " . $conn->error);
        
        // Act
        $stmt->bind_param("s", $studentID);
        $deleteResult = $stmt->execute();
        
        // Assert
        $this->assertTrue($deleteResult, "Failed to delete student: " . $stmt->error);
        
        // Verify deletion
        $checkStmt = $conn->prepare("SELECT * FROM student WHERE Student_ID = ?");
        $checkStmt->bind_param("s", $studentID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        $this->assertEquals(0, $checkResult->num_rows, "Student was not deleted");
        
        // Clean up
        $stmt->close();
        $checkStmt->close();
        $conn->close();
    }

    /**
     * Test attempting to delete a non-existent student
     */
    public function testDeleteNonExistentStudent()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "point_management";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $this->assertNull($conn->connect_error, "Database connection failed");
        
        // Use a student ID that definitely does not exist
        $nonExistentStudentID = 'NONEXISTENT999';
        
        // Prepare the delete statement
        $stmt = $conn->prepare("DELETE FROM student WHERE Student_ID = ?");
        $this->assertNotFalse($stmt, "Failed to prepare delete statement: " . $conn->error);
        
        // Act
        $stmt->bind_param("s", $nonExistentStudentID);
        $deleteResult = $stmt->execute();
        
        // Assert
        $this->assertTrue($deleteResult, "Delete operation failed");
        $this->assertEquals(0, $stmt->affected_rows, "Deleted a non-existent student");
        
        // Clean up
        $stmt->close();
        $conn->close();
    }
}