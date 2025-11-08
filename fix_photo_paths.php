<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Fix Photo Paths</h2>";

if (isset($_POST['fix_paths'])) {
    try {
        // Get all enrollments with photo paths
        $query = "SELECT id, photo_path FROM enrollments WHERE photo_path IS NOT NULL AND photo_path != ''";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $fixed_count = 0;
        
        foreach ($enrollments as $enrollment) {
            $old_path = $enrollment['photo_path'];
            $new_path = $old_path;
            
            // Fix paths that start with ../
            if (strpos($old_path, '../') === 0) {
                $new_path = str_replace('../', '', $old_path);
                
                // Update the database
                $update_query = "UPDATE enrollments SET photo_path = ? WHERE id = ?";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->execute([$new_path, $enrollment['id']]);
                
                echo "<p>✅ Fixed ID {$enrollment['id']}: '$old_path' → '$new_path'</p>";
                $fixed_count++;
            } else {
                echo "<p>ℹ️ ID {$enrollment['id']}: '$old_path' (no change needed)</p>";
            }
        }
        
        echo "<h3>Summary: Fixed $fixed_count photo path(s)</h3>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    // Show current paths
    echo "<h3>Current Photo Paths:</h3>";
    
    try {
        $query = "SELECT id, student_id, first_name, last_name, photo_path FROM enrollments WHERE photo_path IS NOT NULL AND photo_path != ''";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($enrollments)) {
            echo "<p>No enrollments with photo paths found.</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Student</th><th>Current Path</th><th>File Exists?</th></tr>";
            
            foreach ($enrollments as $enrollment) {
                $path = $enrollment['photo_path'];
                $file_exists = file_exists($path) || file_exists('uploads/photos/' . basename($path));
                $status = $file_exists ? '✅ Yes' : '❌ No';
                $color = $file_exists ? 'green' : 'red';
                
                echo "<tr>";
                echo "<td>" . $enrollment['id'] . "</td>";
                echo "<td>" . htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($path) . "</td>";
                echo "<td style='color: $color;'>$status</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<form method='POST' style='margin-top: 20px;'>";
            echo "<button type='submit' name='fix_paths' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Fix Photo Paths</button>";
            echo "</form>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    }
}

echo "<p><a href='view.php?id=1'>Test View Page</a> | <a href='debug_photos.php'>Debug Photos</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f8f9fa; }
</style>
