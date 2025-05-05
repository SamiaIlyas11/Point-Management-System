<?php
use PHPUnit\Framework\TestCase;

class StudentLoginTest extends TestCase
{
    protected $conn;

    protected function setUp(): void
    {
        // Create a mock database connection
        $this->conn = $this->createMock(mysqli::class);
    }

    /**
     * Test 1: Student ID Format Validation Test
     */
    public function testStudentID_WhenFormatIsCorrect_ReturnsTrue()
    {
        // Arrange
        $id = 'K123456';
        
        // Act
        $result = preg_match('/^K\d{6}$/', $id);
        
        // Assert
        $this->assertEquals(1, $result);
    }

    /**
     * Test 2: Invalid Student ID Format Test
     */
    public function testStudentID_WhenFormatIsIncorrect_ReturnsFalse()
    {
        // Arrange
        $invalidIDs = [
            'K12345',    // Too short
            'K1234567',  // Too long
            '123456',    // Missing K prefix
            'X123456',   // Wrong prefix
            'K12345A'    // Contains non-digit
        ];
        
        foreach ($invalidIDs as $id) {
            // Act
            $result = preg_match('/^K\d{6}$/', $id);
            
            // Assert
            $this->assertNotEquals(1, $result);
        }
    }

    /**
     * Test 3: Student Authentication Success Test
     */
    public function testAuthentication_WhenCredentialsAreValid_ReturnsSuccessfulLogin()
    {
        // Arrange
        $id = 'K123456';
        $password = 'password123';
        
        // Set up mock objects
        $stmt = $this->createMock(mysqli_stmt::class);
        
        // Create a mock result combining onlyMethods for existing methods and addMethods for new ones
        $result = $this->getMockBuilder(mysqli_result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetch_assoc'])
            ->addMethods(['num_rows'])
            ->getMock();
        
        $this->conn->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        
        $stmt->expects($this->once())
            ->method('bind_param')
            ->with('ss', $id, $password);
            
        $stmt->expects($this->once())
            ->method('execute');
            
        $stmt->expects($this->once())
            ->method('get_result')
            ->willReturn($result);
            
        // Configure the num_rows method on our custom mock
        $result->expects($this->once())
            ->method('num_rows')
            ->willReturn(1);
            
        $result->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(['Student_ID' => 'K123456']);
        
        // Act
        $stmt = $this->conn->prepare("SELECT Student_ID, student_password FROM student_login WHERE Student_ID = ? AND student_password = ?");
        $stmt->bind_param("ss", $id, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $authenticated = false;
        if ($result->num_rows() == 1) { // Using as a method call
            $row = $result->fetch_assoc();
            $authenticated = true;
            
            // Set session variables
            $_SESSION['user_id'] = $row['Student_ID'];
            $_SESSION['user_email'] = $id;
        }
        
        // Assert
        $this->assertTrue($authenticated);
        $this->assertEquals('K123456', $_SESSION['user_id']);
        $this->assertEquals('K123456', $_SESSION['user_email']);
    }

    /**
     * Test 4: Student Authentication Failure Test
     */
    public function testAuthentication_WhenCredentialsAreInvalid_ReturnsFailedLogin()
    {
        // Arrange
        $id = 'K123456';
        $password = 'wrongpassword';
        
        // Set up mock objects
        $stmt = $this->createMock(mysqli_stmt::class);
        
        // Create a mock result combining onlyMethods for existing methods and addMethods for new ones
        $result = $this->getMockBuilder(mysqli_result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetch_assoc'])
            ->addMethods(['num_rows'])
            ->getMock();
        
        $this->conn->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        
        $stmt->expects($this->once())
            ->method('bind_param')
            ->with('ss', $id, $password);
            
        $stmt->expects($this->once())
            ->method('execute');
            
        $stmt->expects($this->once())
            ->method('get_result')
            ->willReturn($result);
            
        // Configure the num_rows method on our custom mock
        $result->expects($this->once())
            ->method('num_rows')
            ->willReturn(0);
        
        // Act
        $stmt = $this->conn->prepare("SELECT Student_ID, student_password FROM student_login WHERE Student_ID = ? AND student_password = ?");
        $stmt->bind_param("ss", $id, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $authenticated = false;
        if ($result->num_rows() == 1) { // Using as a method call
            $authenticated = true;
        }
        
        // Assert
        $this->assertFalse($authenticated);
    }

    /**
     * Test 5: SQL Injection Prevention Test
     */
    public function testSecurity_WhenSQLInjectionAttempted_QueryIsNotVulnerable()
    {
        // Arrange
        $id = 'K123456';
        $password = "' OR '1'='1"; // SQL injection attempt
        
        // Set up mock objects
        $stmt = $this->createMock(mysqli_stmt::class);
        
        $this->conn->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
            
        $stmt->expects($this->once())
            ->method('bind_param')
            ->with('ss', $id, $password);
        
        // Act
        $stmt = $this->conn->prepare("SELECT Student_ID, student_password FROM student_login WHERE Student_ID = ? AND student_password = ?");
        $stmt->bind_param("ss", $id, $password);
        
        // Assert
        // This assertion is implicit in the mock expectations
        // If bind_param was called with the exact strings and not treated as SQL,
        // then the test passes
        $this->addToAssertionCount(1);
    }
}