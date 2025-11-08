<?php
echo "<h2>Admin Login Route Test</h2>";

// Test the admin login route
$admin_login_url = "http://localhost/SYSTEM/admin/login.php";

echo "<h3>Admin Login Route Configuration:</h3>";
echo "<p><strong>Admin Login URL:</strong> <a href='$admin_login_url' target='_blank'>$admin_login_url</a></p>";

// Check if the admin login file exists
$admin_login_file = 'admin/login.php';
if (file_exists($admin_login_file)) {
    echo "<p style='color: green;'>✅ Admin login file exists: $admin_login_file</p>";
} else {
    echo "<p style='color: red;'>❌ Admin login file not found: $admin_login_file</p>";
}

// Test different access points
echo "<h3>Access Points:</h3>";
echo "<ul>";
echo "<li><strong>Main Landing Page:</strong> <a href='index.php'>index.php</a> → Admin Login button should go to admin/login.php</li>";
echo "<li><strong>Direct Admin Access:</strong> <a href='admin.php'>admin.php</a> → Should redirect to admin/login.php if not logged in</li>";
echo "<li><strong>Admin Dashboard:</strong> <a href='admin/dashboard.php'>admin/dashboard.php</a> → Should redirect to admin/login.php if not logged in</li>";
echo "<li><strong>Admin Students:</strong> <a href='admin/students.php'>admin/students.php</a> → Should redirect to admin/login.php if not logged in</li>";
echo "</ul>";

// Check session manager configuration
echo "<h3>Session Manager Configuration:</h3>";
echo "<p>The SessionManager class has been updated to:</p>";
echo "<ul>";
echo "<li>Detect if the request is from an admin context</li>";
echo "<li>Redirect to appropriate login page based on context</li>";
echo "<li>Use relative paths when already in the correct directory</li>";
echo "</ul>";

// Test credentials
echo "<h3>Admin Login Credentials:</h3>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> admin123</p>";

// Navigation links
echo "<h3>Quick Navigation:</h3>";
echo "<p>";
echo "<a href='index.php' style='margin-right: 10px;'>← Back to Home</a>";
echo "<a href='admin/login.php' style='margin-right: 10px;'>Admin Login</a>";
echo "<a href='student/login.php' style='margin-right: 10px;'>Student Login</a>";
echo "</p>";

// Test logout behavior
echo "<h3>Logout Behavior:</h3>";
echo "<p>When logging out from admin pages, users will be redirected to <code>admin/login.php</code></p>";
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    line-height: 1.6;
}
h2, h3 { 
    color: #333; 
}
code {
    background: #f4f4f4;
    padding: 2px 4px;
    border-radius: 3px;
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>
