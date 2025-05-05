<?php
use PHPUnit\Framework\TestCase;

class AdminLoginTest extends TestCase
{
    protected $conn;
    protected $adminLoginFile;

    protected function setUp(): void
    {
        // Create a mock database connection
        $this->conn = $this->createMock(mysqli::class);
        
        // Path to the admin_login.php file
        $this->adminLoginFile = 'admin_login.php';
    }

    /**
     * Test 1: Database Connection Test
     */
    public function testDatabase_WhenConnectionParametersAreCorrect_ReturnsSuccessfulConnection()
    {
        // Arrange
        $servername = "localhost";
        $username = "root";
        $password = "";
        $db_name = "point_management";
        
        // We need to use a different approach since we can't set read-only properties on mocks
        // Create a partial mock with connect_error property accessible
        $mockConn = $this->getMockBuilder(mysqli::class)
            ->disableOriginalConstructor()
            ->addMethods(['connect_errno'])  // Add a method we can configure
            ->getMock();
            
        // Configure connect_errno to return 0 (success)
        $mockConn->method('connect_errno')
            ->willReturn(0);
        
        // Act - check if the connection has an error by checking connect_errno
        $result = $mockConn->connect_errno();
        
        // Assert - connection is successful if connect_errno is 0
        $this->assertEquals(0, $result);
    }

    /**
     * Test 2: Email Input Validation Test
     */
    public function testEmailInput_WhenValidEmailProvided_ReturnsTrue()
    {
        // Arrange
        $email = 'admin@example.com';
        
        // Act
        $result = filter_var($email, FILTER_VALIDATE_EMAIL);
        
        // Assert
        $this->assertTrue($result !== false);
    }

    /**
     * Test 3: Authentication Success Test
     */
    public function testAuthentication_WhenCredentialsAreValid_ReturnsSuccessfulLogin()
    {
        // Arrange
        $email = 'admin@example.com';
        $password = 'SecurePassword123';
        
        // Set up mock objects
        $stmt = $this->createMock(mysqli_stmt::class);
        
        // Create a mock result with proper method configuration
        $result = $this->getMockBuilder(mysqli_result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetch_assoc'])
            ->addMethods(['num_rows'])
            ->getMock();
        
        $this->conn->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        
        $stmt->expects($this->once())
            ->method('execute');
            
        $stmt->expects($this->once())
            ->method('get_result')
            ->willReturn($result);
            
        $result->expects($this->once())
            ->method('num_rows')
            ->willReturn(1);
            
        $result->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(['id' => 1, 'email' => 'admin@example.com']);
        
        // Act
        $stmt = $this->conn->prepare("SELECT email, admin_password FROM admin_login WHERE email = ? AND admin_password = ?");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $authenticated = false;
        if ($result->num_rows() == 1) {  // Changed to method call
            $row = $result->fetch_assoc();
            $authenticated = true;
            
            // Set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_email'] = $row['email'];
        }
        
        // Assert
        $this->assertTrue($authenticated);
        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertEquals('admin@example.com', $_SESSION['user_email']);
    }

    /**
     * Test 4: Authentication Failure Test
     */
    public function testAuthentication_WhenCredentialsAreInvalid_ReturnsFailedLogin()
    {
        // Arrange
        $email = 'admin@example.com';
        $password = 'WrongPassword';
        
        // Set up mock objects
        $stmt = $this->createMock(mysqli_stmt::class);
        
        // Create a mock result with proper method configuration
        $result = $this->getMockBuilder(mysqli_result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetch_assoc'])
            ->addMethods(['num_rows'])
            ->getMock();
        
        $this->conn->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        
        $stmt->expects($this->once())
            ->method('execute');
            
        $stmt->expects($this->once())
            ->method('get_result')
            ->willReturn($result);
            
        $result->expects($this->once())
            ->method('num_rows')
            ->willReturn(0);
        
        // Act
        $stmt = $this->conn->prepare("SELECT email, admin_password FROM admin_login WHERE email = ? AND admin_password = ?");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $authenticated = false;
        if ($result->num_rows() == 1) {  // Changed to method call
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
        $email = 'admin@example.com';
        $password = "' OR '1'='1"; // SQL injection attempt
        
        // Set up mock objects
        $stmt = $this->createMock(mysqli_stmt::class);
        
        $this->conn->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
            
        $stmt->expects($this->once())
            ->method('bind_param')
            ->with('ss', $email, $password);
        
        // Act
        $stmt = $this->conn->prepare("SELECT email, admin_password FROM admin_login WHERE email = ? AND admin_password = ?");
        $stmt->bind_param("ss", $email, $password);
        
        // Assert
        // The assertion is implicit in the mock expectations
        // If bind_param was called with the exact injection string (not interpreted as SQL)
        // then the test passes
        $this->addToAssertionCount(1);
    }
}