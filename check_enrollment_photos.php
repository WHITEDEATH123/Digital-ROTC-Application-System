<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Enrollment Photo Check</h2>";

// Get all enrollments with their photo paths
$query = "SELECT id, student_id, first_name, last_name, photo_path, status, created_at FROM enrollments ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($enrollments)) {
    echo "<p style='color: orange;'>No enrollments found in database.</p>";
    echo "<p><a href='create_test_data.php'>Create Test Data</a></p>";
} else {
    echo "<p>Found " . count($enrollments) . " enrollment(s):</p>";
    
    foreach ($enrollments as $enrollment) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h3>Enrollment ID: " . $enrollment['id'] . "</h3>";
        echo "<p><strong>Student:</strong> " . htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) . "</p>";
        echo "<p><strong>Student ID:</strong> " . htmlspecialchars($enrollment['student_id']) . "</p>";
        echo "<p><strong>Status:</strong> " . $enrollment['status'] . "</p>";
        echo "<p><strong>Photo Path:</strong> " . ($enrollment['photo_path'] ? htmlspecialchars($enrollment['photo_path']) : 'NULL') . "</p>";
        
        if ($enrollment['photo_path']) {
            $photoPath = $enrollment['photo_path'];
            echo "<p><strong>File Check:</strong> ";
            
            if (file_exists($photoPath)) {
                echo "<span style='color: green;'>✅ File exists at: $photoPath</span>";
            } elseif (file_exists('./' . $photoPath)) {
                echo "<span style='color: green;'>✅ File exists at: ./$photoPath</span>";
            } elseif (file_exists('uploads/photos/' . basename($photoPath))) {
                echo "<span style='color: green;'>✅ File exists at: uploads/photos/" . basename($photoPath) . "</span>";
            } else {
                echo "<span style='color: red;'>❌ File not found</span>";
            }
            echo "</p>";
        }
        
        echo "<p><a href='view.php?id=" . $enrollment['id'] . "' target='_blank'>View Details</a></p>";
        echo "</div>";
    }
}

// Check what files are in uploads/photos
echo "<h3>Files in uploads/photos/:</h3>";
$uploadDir = 'uploads/photos/';
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<p>📁 $file</p>";
        }
    }
} else {
    echo "<p style='color: red;'>Directory not found</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
</style>
