<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Debug: Database Contents</h2>";

// Check database connection
try {
    $db->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Check users table
echo "<h3>Users Table:</h3>";
try {
    $query = "SELECT * FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p>No users found in database.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Role</th><th>Name</th><th>Email</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . htmlspecialchars($user['name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['email'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>Error querying users: " . $e->getMessage() . "</p>";
}

// Check enrollments table
echo "<h3>Enrollments Table:</h3>";
try {
    $query = "SELECT * FROM enrollments ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($enrollments)) {
        echo "<p>No enrollments found in database.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Student ID</th><th>Name</th><th>Course</th><th>Status</th><th>Created</th></tr>";
        foreach ($enrollments as $enrollment) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($enrollment['id']) . "</td>";
            echo "<td>" . htmlspecialchars($enrollment['student_id']) . "</td>";
            echo "<td>" . htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($enrollment['course'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($enrollment['status']) . "</td>";
            echo "<td>" . htmlspecialchars($enrollment['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>Error querying enrollments: " . $e->getMessage() . "</p>";
}

// Check table structure
echo "<h3>Enrollments Table Structure:</h3>";
try {
    $query = "DESCRIBE enrollments";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p>Error getting table structure: " . $e->getMessage() . "</p>";
}
?>

<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
