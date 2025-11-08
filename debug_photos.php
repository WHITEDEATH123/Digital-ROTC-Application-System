<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Photo Debug Information</h2>";

// Check uploads directory
$uploadDir = 'uploads/photos/';
echo "<h3>Upload Directory Contents:</h3>";
if (is_dir($uploadDir)) {
    echo "<p style='color: green;'>✅ Directory exists: $uploadDir</p>";
    $files = scandir($uploadDir);
    if (count($files) > 2) { // . and .. are always present
        echo "<ul>";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $uploadDir . $file;
                $fileSize = filesize($filePath);
                echo "<li><strong>$file</strong> - " . number_format($fileSize) . " bytes</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ Directory is empty</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Directory does not exist: $uploadDir</p>";
}

// Check database photo paths
echo "<h3>Database Photo Paths:</h3>";
try {
    $query = "SELECT id, student_id, first_name, last_name, photo_path, status FROM enrollments WHERE photo_path IS NOT NULL AND photo_path != ''";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($enrollments)) {
        echo "<p style='color: orange;'>⚠️ No enrollments with photo paths found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Student</th><th>Photo Path</th><th>File Exists?</th><th>Status</th></tr>";
        
        foreach ($enrollments as $enrollment) {
            $photoPath = $enrollment['photo_path'];
            $fileExists = false;
            $actualPath = '';
            
            // Check various possible paths
            if (file_exists($photoPath)) {
                $fileExists = true;
                $actualPath = $photoPath;
            } elseif (file_exists('./' . $photoPath)) {
                $fileExists = true;
                $actualPath = './' . $photoPath;
            } elseif (file_exists('uploads/photos/' . basename($photoPath))) {
                $fileExists = true;
                $actualPath = 'uploads/photos/' . basename($photoPath);
            }
            
            $statusColor = $fileExists ? 'green' : 'red';
            $statusIcon = $fileExists ? '✅' : '❌';
            
            echo "<tr>";
            echo "<td>" . $enrollment['id'] . "</td>";
            echo "<td>" . htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($photoPath) . "</td>";
            echo "<td style='color: $statusColor;'>$statusIcon " . ($fileExists ? "Yes ($actualPath)" : "No") . "</td>";
            echo "<td>" . $enrollment['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Check permissions
echo "<h3>Directory Permissions:</h3>";
if (is_dir($uploadDir)) {
    if (is_readable($uploadDir)) {
        echo "<p style='color: green;'>✅ Directory is readable</p>";
    } else {
        echo "<p style='color: red;'>❌ Directory is not readable</p>";
    }
    
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✅ Directory is writable</p>";
    } else {
        echo "<p style='color: red;'>❌ Directory is not writable</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Cannot check permissions - directory does not exist</p>";
}

// Test photo creation
echo "<h3>Test Photo Upload:</h3>";
echo "<p><a href='student/enroll.php'>Go to Enrollment Form</a> to test photo upload</p>";
echo "<p><a href='view.php?id=1'>View Sample Enrollment</a> (if exists)</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f8f9fa; }
</style>
