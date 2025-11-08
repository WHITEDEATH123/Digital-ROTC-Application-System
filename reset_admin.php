<?php
/**
 * Quick Admin Reset Script
 * Use this to reset the admin password if login is not working
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Admin Password Reset</h2>";
    
    // Create/update admin user with correct password
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // First, try to update existing admin
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = 'admin' AND role = 'admin'");
    $stmt->execute([$admin_password]);
    
    if ($stmt->rowCount() == 0) {
        // Admin doesn't exist, create it
        $stmt = $db->prepare("INSERT INTO users (id, password, role, name, email) VALUES (?, ?, 'admin', 'Administrator', 'admin@rotc.edu')");
        $stmt->execute(['admin', $admin_password]);
        echo "<p style='color: green;'>✓ Admin user created successfully!</p>";
    } else {
        echo "<p style='color: green;'>✓ Admin password updated successfully!</p>";
    }
    
    // Verify admin exists
    $stmt = $db->prepare("SELECT id, role, name FROM users WHERE id = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Admin Account Details:</h3>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>Role:</strong> " . $admin['role'] . "</p>";
        echo "<p><strong>Name:</strong> " . $admin['name'] . "</p>";
        echo "</div>";
        
        echo "<p><a href='login.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    } else {
        echo "<p style='color: red;'>Error: Could not verify admin account</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<h3>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Run install.php first to create the database</li>";
    echo "<li>Check database connection settings in config/database.php</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Reset - ROTC System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <!-- PHP output appears here -->
</body>
</html>
