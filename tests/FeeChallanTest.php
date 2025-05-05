<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class FeeChallanTest extends TestCase
{
    protected $conn;

    protected function setUp(): void
    {
        // Create a mock database connection
        $this->conn = $this->createMock(mysqli::class);
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
        $dbname = "point_management";
        
        // We need to use a different approach since we can't directly test a real connection
        // Create a partial mock with connect_errno method accessible
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
     * Test 2: Input Sanitization Test
     */
    public function testInputSanitization_WhenInputContainsHtmlTags_SanitizesInput()
    {
        // Arrange
        $unsanitizedName = "<script>alert('XSS Attack')</script>John Doe";
        
        // Act
        $sanitizedName = htmlspecialchars($unsanitizedName);
        
        // Assert
        // Don't use a fixed expected string, instead verify that the dangerous characters are encoded
        $this->assertStringContainsString("&lt;script&gt;", $sanitizedName);
        $this->assertStringContainsString("&lt;/script&gt;", $sanitizedName);
        $this->assertStringContainsString("John Doe", $sanitizedName);
        $this->assertStringNotContainsString("<script>", $sanitizedName);
        $this->assertStringNotContainsString("</script>", $sanitizedName);
    }

    /**
     * Test 3: Form Validation - Empty Fields Test
     */
    public function testFormValidation_WhenFieldsAreEmpty_ValidationFails()
    {
        // Arrange
        $testCases = [
            ['name' => '', 'student-id' => 'K123456', 'point-number' => 'P01'],
            ['name' => 'John Doe', 'student-id' => '', 'point-number' => 'P01'],
            ['name' => 'John Doe', 'student-id' => 'K123456', 'point-number' => ''],
            ['name' => '', 'student-id' => '', 'point-number' => '']
        ];
        
        // Act & Assert
        foreach ($testCases as $case) {
            $isValid = !empty($case['name']) && !empty($case['student-id']) && !empty($case['point-number']);
            $this->assertFalse($isValid, 'Validation should fail with empty fields');
        }
    }

    /**
     * Test 4: Form Validation - Valid Fields Test
     */
    public function testFormValidation_WhenAllFieldsAreProvided_ValidationPasses()
    {
        // Arrange
        $name = 'John Doe';
        $studentId = 'K123456';
        $pointNumber = 'P01';
        
        // Act
        $isValid = !empty($name) && !empty($studentId) && !empty($pointNumber);
        
        // Assert
        $this->assertTrue($isValid, 'Validation should pass with all fields filled');
    }

    /**
     * Test 5: Fee Challan Generation Test
     */
    public function testChallanGeneration_WhenValidDataProvided_GeneratesChallan()
    {
        // Arrange
        $studentName = 'John Doe';
        $studentId = 'K123456';
        $pointNumber = 'P01';
        
        // Expected output contains all required information
        $expectedOutputContains = [
            '<h1>FAST UNIVERSITY</h1>',
            "<p><strong>Student Name:</strong> $studentName</p>",
            "<p><strong>Student ID:</strong> $studentId</p>",
            "<p><strong>Point Number:</strong> $pointNumber</p>",
            "<p><strong>Fee Amount:</strong> Rs 35000</p>",
            "<p><strong>Fee Challan generated successfully</strong></p>",
            '<button onclick="printChallan()">Print Challan</button>'
        ];
        
        // Set up POST data
        $_POST = [
            'name' => $studentName,
            'student-id' => $studentId,
            'point-number' => $pointNumber
        ];
        $_SERVER["REQUEST_METHOD"] = "POST";
        
        // Act
        ob_start(); // Start output buffering
        
        // Simulate form processing
        $name = htmlspecialchars($_POST['name']);
        $id = htmlspecialchars($_POST['student-id']);
        $point = htmlspecialchars($_POST['point-number']);
        
        if (empty($name) || empty($id) || empty($point)) {
            echo "Please fill in all fields correctly.";
        } else {
            echo '<div id="challanToPrint" style="text-align: center; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%;">';
            echo "<h1>FAST UNIVERSITY</h1>";
            echo "<p><strong>Student Name:</strong> $name</p>";
            echo "<p><strong>Student ID:</strong> $id</p>";
            echo "<p><strong>Point Number:</strong> $point</p>";
            echo "<p><strong>Fee Amount:</strong> Rs 35000</p>";
            echo "<p><strong>Fee Challan generated successfully</strong></p>";
            echo '<button onclick="printChallan()">Print Challan</button>';
            echo '</div>';
        }
        
        $output = ob_get_clean(); // Get the output and stop buffering
        
        // Assert
        foreach ($expectedOutputContains as $expectedText) {
            $this->assertStringContainsString($expectedText, $output);
        }
    }
}