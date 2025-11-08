<?php
/**
 * Debug Script - Check system status
 */

echo "<h2>ROTC System Debug Information</h2>";
echo "<style>body{font-family:Arial;margin:40px;} .ok{color:green;} .error{color:red;} .info{background:#f0f0f0;padding:10px;margin:10px 0;}</style>";

// Check PHP version
echo "<h3>1. PHP Version</h3>";
echo "<div class='info'>PHP Version: " . phpversion() . "</div>";

// Check required extensions
echo "<h3>2. Required Extensions</h3>";
$extensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='ok'>✓ {$ext} - OK</div>";
    } else {
        echo "<div class='error'>✗ {$ext} - MISSING</div>";
    }
}

// Check database connection
echo "<h3>3. Database Connection</h3>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "<div class='ok'>✓ MySQL Connection - OK</div>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'rotc_system'");
    if ($stmt->fetch()) {
        echo "<div class='ok'>✓ Database 'rotc_system' - EXISTS</div>";
        
        // Connect to the database
        $pdo = new PDO("mysql:host=localhost;dbname=rotc_system", "root", "");
        
        // Check if tables exist
        $tables = ['users', 'enrollments', 'sessions'];
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->fetch()) {
                echo "<div class='ok'>✓ Table '{$table}' - EXISTS</div>";
            } else {
                echo "<div class='error'>✗ Table '{$table}' - MISSING</div>";
            }
        }
        
        // Check admin user
        $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<div class='ok'>✓ Admin user - EXISTS (Role: {$admin['role']})</div>";
        } else {
            echo "<div class='error'>✗ Admin user - MISSING</div>";
        }
        
    } else {
        echo "<div class='error'>✗ Database 'rotc_system' - NOT FOUND</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>✗ Database Connection Failed: " . $e->getMessage() . "</div>";
}

// Check file permissions
echo "<h3>4. File System</h3>";
$files = ['config/database.php', 'login.php', 'install.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<div class='ok'>✓ {$file} - EXISTS</div>";
    } else {
        echo "<div class='error'>✗ {$file} - MISSING</div>";
    }
}

// Check uploads directory
if (is_dir('uploads/photos')) {
    if (is_writable('uploads/photos')) {
        echo "<div class='ok'>✓ uploads/photos - WRITABLE</div>";
    } else {
        echo "<div class='error'>✗ uploads/photos - NOT WRITABLE</div>";
    }
} else {
    echo "<div class='error'>✗ uploads/photos - DIRECTORY MISSING</div>";
}

echo "<h3>5. Quick Actions</h3>";
echo "<p><a href='install.php' style='background:#007bff;color:white;padding:10px;text-decoration:none;border-radius:3px;margin-right:10px;'>Run Install</a>";
echo "<a href='reset_admin.php' style='background:#28a745;color:white;padding:10px;text-decoration:none;border-radius:3px;margin-right:10px;'>Reset Admin</a>";
echo "<a href='login.php' style='background:#6c757d;color:white;padding:10px;text-decoration:none;border-radius:3px;'>Go to Login</a></p>";
?>
