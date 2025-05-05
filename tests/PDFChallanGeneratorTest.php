<?php
use PHPUnit\Framework\TestCase;
class PDFChallanGeneratorTest extends TestCase
{
    /**
     * Test 1: Generate Challan Function Test
     * Tests that the generateChallan function creates an HTML challan with student details
     */
    public function testGenerateChallan_WhenCalled_CreatesCorrectHTML()
    {
        // Arrange
        require_once('ChallanGenerator.php');
        $studentId = '12345';
        $studentName = 'John Doe';
        $expectedFileName = $studentId . '_challan.html';
        
        // Act
        $actualFileName = ChallanGenerator::generateChallan($studentId, $studentName);
        
        // Assert
        $this->assertEquals($expectedFileName, $actualFileName);
        
        // Clean up
        if (file_exists($expectedFileName)) {
            unlink($expectedFileName);
        }
    }
    /**
     * Test 2: File Output Test
     * Tests that the challan is saved to the expected file location
     */
    public function testGenerateChallan_WhenCalled_OutputsToCorrectFile()
    {
        // Arrange
        require_once('ChallanGenerator.php');
        $studentId = '12345';
        $studentName = 'John Doe';
        $expectedFileName = $studentId . '_challan.html';
        
        // Act
        ChallanGenerator::generateChallan($studentId, $studentName);
        
        // Assert
        $this->assertFileExists($expectedFileName);
        
        // Clean up
        if (file_exists($expectedFileName)) {
            unlink($expectedFileName);
        }
    }
    /**
     * Test 3: Content Verification Test
     * Tests that the generated HTML contains the expected student information
     */
    public function testGenerateChallan_WhenGeneratingHTML_ContainsCorrectStudentInfo()
    {
        // Arrange
        require_once('ChallanGenerator.php');
        $studentId = '12345';
        $studentName = 'John Doe';
        $expectedFileName = $studentId . '_challan.html';
        
        // Act
        ChallanGenerator::generateChallan($studentId, $studentName);
        $fileContent = file_get_contents($expectedFileName);
        
        // Assert
        $this->assertStringContainsString('Student ID: ' . $studentId, $fileContent);
        $this->assertStringContainsString('Student Name: ' . $studentName, $fileContent);
        
        // Clean up
        if (file_exists($expectedFileName)) {
            unlink($expectedFileName);
        }
    }
    
    /**
     * Test 4: Special Characters Handling Test
     * Tests that the generateChallan function properly escapes special characters
     */
    public function testGenerateChallan_WhenInputHasSpecialCharacters_EscapesCorrectly()
    {
        // Arrange
        require_once('ChallanGenerator.php');
        $studentId = '12345';
        $studentName = 'John Doe <script>alert("XSS")</script>';
        $expectedFileName = $studentId . '_challan.html';
        
        // Act
        ChallanGenerator::generateChallan($studentId, $studentName);
        $fileContent = file_get_contents($expectedFileName);
        
        // Assert
        $this->assertStringContainsString('Student Name: ' . htmlspecialchars($studentName), $fileContent);
        $this->assertStringNotContainsString('<script>alert("XSS")</script>', $fileContent);
        
        // Clean up
        if (file_exists($expectedFileName)) {
            unlink($expectedFileName);
        }
    }
    
    /**
     * Test 5: HTML Structure Validation Test
     * Tests that the generated HTML has the correct structure and elements
     */
    public function testGenerateChallan_WhenGenerated_HasCorrectHTMLStructure()
    {
        // Arrange
        require_once('ChallanGenerator.php');
        $studentId = '12345';
        $studentName = 'John Doe';
        $expectedFileName = $studentId . '_challan.html';
        
        // Act
        ChallanGenerator::generateChallan($studentId, $studentName);
        $fileContent = file_get_contents($expectedFileName);
        
        // Assert
        $this->assertStringContainsString('<!DOCTYPE html>', $fileContent);
        $this->assertStringContainsString('<html>', $fileContent);
        $this->assertStringContainsString('<head>', $fileContent);
        $this->assertStringContainsString('<title>Fee Challan</title>', $fileContent);
        $this->assertStringContainsString('<style>', $fileContent);
        $this->assertStringContainsString('<body>', $fileContent);
        $this->assertStringContainsString('<div class="header">Fee Challan</div>', $fileContent);
        $this->assertStringContainsString('<div class="content">', $fileContent);
        $this->assertStringContainsString('<div class="footer">', $fileContent);
        
        // Clean up
        if (file_exists($expectedFileName)) {
            unlink($expectedFileName);
        }
    }
    
    /**
     * Test 6: Date Generation Test
     * Tests that the challan includes the current date in the footer
     */
    public function testGenerateChallan_WhenGenerated_IncludesCurrentDate()
    {
        // Arrange
        require_once('ChallanGenerator.php');
        $studentId = '12345';
        $studentName = 'John Doe';
        $expectedFileName = $studentId . '_challan.html';
        $currentDate = date('Y-m-d');
        
        // Act
        ChallanGenerator::generateChallan($studentId, $studentName);
        $fileContent = file_get_contents($expectedFileName);
        
        // Assert
        $this->assertStringContainsString($currentDate, $fileContent);
        
        // Clean up
        if (file_exists($expectedFileName)) {
            unlink($expectedFileName);
        }
    }
}