<?php
/**
 * System Test Script
 * Tests basic functionality of the Digital ROTC System
 */

require_once 'config/database.php';

echo "<h2>Digital ROTC System - Test Results</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    .test-pass { color: green; }
    .test-fail { color: red; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
</style>";

$tests_passed = 0;
$tests_total = 0;

function runTest($description, $test_function) {
    global $tests_passed, $tests_total;
    $tests_total++;
    
    echo "<div class='test-section'>";
    echo "<strong>Test {$tests_total}: {$description}</strong><br>";
    
    try {
        $result = $test_function();
        if ($result) {
            echo "<span class='test-pass'>✓ PASS</span>";
            $tests_passed++;
        } else {
            echo "<span class='test-fail'>✗ FAIL</span>";
        }
    } catch (Exception $e) {
        echo "<span class='test-fail'>✗ FAIL - " . $e->getMessage() . "</span>";
    }
    
    echo "</div>";
}

// Test 1: Database Connection
runTest("Database Connection", function() {
    $database = new Database();
    $db = $database->getConnection();
    return $db !== null;
});

// Test 2: Tables Exist
runTest("Database Tables Exist", function() {
    $database = new Database();
    $db = $database->getConnection();
    
    $tables = ['users', 'enrollments', 'sessions'];
    foreach ($tables as $table) {
        $stmt = $db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if (!$stmt->fetch()) {
            throw new Exception("Table {$table} not found");
        }
    }
    return true;
});

// Test 3: Admin User Exists
runTest("Admin User Exists", function() {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = 'admin' AND role = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    return $admin !== false;
});

// Test 4: Session Manager
runTest("Session Manager Functionality", function() {
    $sessionManager = new SessionManager();
    return $sessionManager !== null;
});

// Test 5: File Upload Directory
runTest("Upload Directory Exists and Writable", function() {
    $uploadDir = 'uploads/photos/';
    
    if (!is_dir($uploadDir)) {
        throw new Exception("Upload directory does not exist");
    }
    
    if (!is_writable($uploadDir)) {
        throw new Exception("Upload directory is not writable");
    }
    
    return true;
});

// Test 6: Required PHP Extensions
runTest("Required PHP Extensions", function() {
    $required_extensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo'];
    
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            throw new Exception("PHP extension {$ext} not loaded");
        }
    }
    
    return true;
});

// Test 7: File Permissions
runTest("File Permissions", function() {
    $files_to_check = [
        'config/database.php',
        'login.php',
        'dashboard.php',
        'enroll.php',
        'admin.php'
    ];
    
    foreach ($files_to_check as $file) {
        if (!file_exists($file)) {
            throw new Exception("File {$file} not found");
        }
        if (!is_readable($file)) {
            throw new Exception("File {$file} not readable");
        }
    }
    
    return true;
});

// Test 8: Create Test Student
runTest("Create Test Student Account", function() {
    $database = new Database();
    $db = $database->getConnection();
    
    // Clean up any existing test user
    $stmt = $db->prepare("DELETE FROM users WHERE id = 'test_student'");
    $stmt->execute();
    
    // Create test student
    $password = password_hash('test123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (id, password, role, name) VALUES (?, ?, 'student', ?)");
    $result = $stmt->execute(['test_student', $password, 'Test Student']);
    
    return $result;
});

// Test 9: Password Verification
runTest("Password Hashing and Verification", function() {
    $password = 'test123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    return password_verify($password, $hash);
});

// Test 10: Utility Functions
runTest("Utility Functions", function() {
    $test_input = "<script>alert('xss')</script>";
    $sanitized = sanitizeInput($test_input);
    
    return $sanitized !== $test_input && !strpos($sanitized, '<script>');
});

// Summary
echo "<div style='margin-top: 30px; padding: 20px; background-color: " . 
     ($tests_passed === $tests_total ? '#d4edda' : '#f8d7da') . 
     "; border-radius: 5px;'>";
echo "<h3>Test Summary</h3>";
echo "<p><strong>Tests Passed:</strong> {$tests_passed} / {$tests_total}</p>";

if ($tests_passed === $tests_total) {
    echo "<p style='color: green;'><strong>✓ All tests passed! System is ready for use.</strong></p>";
    echo "<p><a href='login.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
} else {
    echo "<p style='color: red;'><strong>✗ Some tests failed. Please check the system configuration.</strong></p>";
    echo "<p><strong>Common Solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Run install.php to set up the database</li>";
    echo "<li>Check XAMPP services are running</li>";
    echo "<li>Verify file permissions</li>";
    echo "<li>Check PHP configuration</li>";
    echo "</ul>";
}

echo "</div>";

// Clean up test data
try {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("DELETE FROM users WHERE id = 'test_student'");
    $stmt->execute();
} catch (Exception $e) {
    // Ignore cleanup errors
}
?>
