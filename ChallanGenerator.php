<?php
/**
 * PDF Generator class that uses HTML/CSS for generating fee challans
 * This eliminates the need for FPDF
 */
class ChallanGenerator
{
    /**
     * Generates a fee challan for a student as an HTML file
     * 
     * @param string $studentId The ID of the student
     * @param string $studentName The name of the student
     * @return string The filename of the generated HTML challan
     */
    public static function generateChallan($studentId, $studentName)
    {
        // Generate filename
        $filename = $studentId . '_challan.html';
        
        // Create HTML content
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fee Challan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 20px;
            padding: 10px;
            border-bottom: 1px solid #000;
        }
        .content {
            margin-bottom: 30px;
        }
        .info {
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }
        @media print {
            body {
                width: 210mm;
                height: 297mm;
            }
        }
    </style>
</head>
<body>
    <div class="header">Fee Challan</div>
    
    <div class="content">
        <div class="info">Student ID: ' . htmlspecialchars($studentId) . '</div>
        <div class="info">Student Name: ' . htmlspecialchars($studentName) . '</div>
    </div>
    
    <div class="footer">
        This document was generated on ' . date('Y-m-d H:i:s') . '
    </div>
</body>
</html>';
        
        // Save to file
        file_put_contents($filename, $html);
        
        return $filename;
    }
}