<?php
/**
 * Authentication service class to handle student login
 */
class AuthenticationService {
    private $db;
    
    /**
     * Constructor accepts a database connection
     */
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }
    
    /**
     * Authenticate a student with ID and password
     * 
     * @param string $id Student ID
     * @param string $password Student password
     * @return array|false Student data array if authentication succeeds, false otherwise
     */
    public function authenticate($id, $password) {
        // Validate student ID format
        if (!preg_match('/^K\d{6}$/', $id)) {
            return false;
        }
        
        // Query database
        $stmt = $this->db->prepare("SELECT * FROM student WHERE Student_ID = ? AND student_password = ?");
        $stmt->bind_param("ss", $id, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if credentials are valid
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Start a session with student data
     * 
     * @param array $studentData Student data to store in session
     */
    public function startSession($studentData) {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Store student data in session
        $_SESSION = $studentData;
    }
}