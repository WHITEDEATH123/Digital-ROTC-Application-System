<?php
// Debug script for enrollment form issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Enrollment Form Debug</h2>";

// Test database connection
try {
    require_once 'config/database.php';
    echo "<p style='color: green;'>✅ Database config loaded successfully</p>";
    
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test session
try {
    $sessionManager = new SessionManager();
    echo "<p style='color: green;'>✅ SessionManager loaded successfully</p>";
    
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        echo "<p style='color: green;'>✅ User logged in: " . $_SESSION['user_id'] . "</p>";
        echo "<p>Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No user logged in</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Session error: " . $e->getMessage() . "</p>";
}

// Test file permissions
$uploadDir = 'uploads/photos/';
if (is_dir($uploadDir)) {
    echo "<p style='color: green;'>✅ Upload directory exists: $uploadDir</p>";
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✅ Upload directory is writable</p>";
    } else {
        echo "<p style='color: red;'>❌ Upload directory is not writable</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Upload directory does not exist: $uploadDir</p>";
}

// Test CSS files
$cssFiles = [
    'assets/css/enroll.css',
    'assets/css/green-theme.css'
];

foreach ($cssFiles as $cssFile) {
    if (file_exists($cssFile)) {
        echo "<p style='color: green;'>✅ CSS file exists: $cssFile</p>";
    } else {
        echo "<p style='color: red;'>❌ CSS file missing: $cssFile</p>";
    }
}

// Test image files
$imageFiles = [
    'assets/img/jhcsc-logo.png',
    'assets/img/ro.png'
];

foreach ($imageFiles as $imageFile) {
    if (file_exists($imageFile)) {
        echo "<p style='color: green;'>✅ Image file exists: $imageFile</p>";
    } else {
        echo "<p style='color: red;'>❌ Image file missing: $imageFile</p>";
    }
}

// Test enrollment form access
echo "<h3>Direct Form Access Test:</h3>";
echo "<p><a href='student/enroll.php' target='_blank'>Test Enrollment Form</a></p>";

// Check if user needs to login first
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: orange;'>⚠️ You may need to login first:</p>";
    echo "<p><a href='student/login.php'>Student Login</a></p>";
    echo "<p><a href='admin/login.php'>Admin Login</a></p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
</style>
