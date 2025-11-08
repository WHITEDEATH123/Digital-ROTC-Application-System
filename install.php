<?php
/**
 * Installation Script for Digital ROTC System
 * Run this file once to set up the database
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'rotc_system';

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Digital ROTC System Installation</h2>";
    echo "<p>Setting up database...</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    echo "✓ Database '$database' created successfully<br>";
    
    // Use the database
    $pdo->exec("USE $database");
    
    // Read and execute schema
    $schema = file_get_contents('database/schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✓ Database tables created successfully<br>";
    
    // Create admin user with hashed password
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (id, password, role, name, email) VALUES (?, ?, 'admin', 'Administrator', 'admin@rotc.edu') ON DUPLICATE KEY UPDATE password = VALUES(password)");
    $stmt->execute(['admin', $admin_password]);
    
    echo "✓ Admin user configured (admin/admin123)<br>";
    
    // Create uploads directory if it doesn't exist
    if (!is_dir('uploads/photos')) {
        mkdir('uploads/photos', 0755, true);
        echo "✓ Uploads directory created<br>";
    }
    
    echo "<br><div style='background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Installation Complete!</h3>";
    echo "<p><strong>Default Admin Credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>admin123</code></li>";
    echo "</ul>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Delete this install.php file for security</li>";
    echo "<li>Access the system at <a href='login.php'>login.php</a></li>";
    echo "<li>Students can create accounts automatically by entering their Student ID</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p><a href='login.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Installation Failed!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Common Solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP/MySQL is running</li>";
    echo "<li>Check database credentials in config/database.php</li>";
    echo "<li>Ensure MySQL user has CREATE DATABASE privileges</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROTC System Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        h2 {
            color: #333;
            text-align: center;
        }
        
        code {
            background-color: #e9ecef;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }
        
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- PHP output appears here -->
    </div>
</body>
</html>
