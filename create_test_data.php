<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Create Test Data</h2>";

// Check if we should create test data
if (isset($_POST['create_test_data'])) {
    try {
        // Create test students
        $test_students = [
            [
                'student_id' => '2024-0001',
                'first_name' => 'Juan',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'age' => 19,
                'course' => 'BSIT',
                'block' => 'A',
                'email' => 'juan.delacruz@student.jhcsc.edu.ph',
                'phone' => '09123456789'
            ],
            [
                'student_id' => '2024-0002',
                'first_name' => 'Maria',
                'middle_name' => 'Garcia',
                'last_name' => 'Rodriguez',
                'age' => 20,
                'course' => 'NURSING',
                'block' => 'B',
                'email' => 'maria.rodriguez@student.jhcsc.edu.ph',
                'phone' => '09987654321'
            ],
            [
                'student_id' => '2024-0003',
                'first_name' => 'Jose',
                'middle_name' => 'Miguel',
                'last_name' => 'Santos',
                'age' => 18,
                'course' => 'BSHM',
                'block' => 'A',
                'email' => 'jose.santos@student.jhcsc.edu.ph',
                'phone' => '09555666777'
            ]
        ];

        foreach ($test_students as $student) {
            // First create user account
            $user_query = "INSERT IGNORE INTO users (id, password, role, name, email) VALUES (?, ?, 'student', ?, ?)";
            $user_stmt = $db->prepare($user_query);
            $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
            $full_name = $student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name'];
            $user_stmt->execute([$student['student_id'], $hashed_password, $full_name, $student['email']]);

            // Then create enrollment record
            $enrollment_query = "INSERT IGNORE INTO enrollments (
                student_id, first_name, middle_name, last_name, age, religion,
                date_of_birth, place_of_birth, height, weight, complexion, blood_type,
                block, course, army_nstp, address, phone, email,
                father_name, father_occupation, mother_name, mother_occupation,
                emergency_contact_person, relationship, emergency_contact_number,
                ms, semester, school_year, grade, ms_remarks, advance_course,
                photo_path, status, submitted_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', NOW())";
            
            $enrollment_stmt = $db->prepare($enrollment_query);
            $enrollment_stmt->execute([
                $student['student_id'],
                $student['first_name'],
                $student['middle_name'],
                $student['last_name'],
                $student['age'],
                'Catholic', // religion
                '2005-01-01', // date_of_birth
                'Manila, Philippines', // place_of_birth
                170.5, // height
                65.0, // weight
                'Fair', // complexion
                'O', // blood_type
                $student['block'],
                $student['course'],
                'ROTC', // army_nstp
                '123 Sample Street, Manila, Philippines', // address
                $student['phone'],
                $student['email'],
                'Father Name', // father_name
                'Engineer', // father_occupation
                'Mother Name', // mother_name
                'Teacher', // mother_occupation
                'Emergency Contact', // emergency_contact_person
                'Parent', // relationship
                '09111222333', // emergency_contact_number
                'MS1', // ms
                '1st Semester', // semester
                '2023-2024', // school_year
                '1.5', // grade
                'Good performance', // ms_remarks
                0, // advance_course
                'uploads/photos/default.jpg' // photo_path
            ]);
        }

        echo "<p style='color: green;'>✅ Test data created successfully!</p>";
        echo "<p>Created 3 test students with the following credentials:</p>";
        echo "<ul>";
        foreach ($test_students as $student) {
            echo "<li><strong>" . $student['student_id'] . "</strong> - " . $student['first_name'] . " " . $student['last_name'] . " (Password: password123)</li>";
        }
        echo "</ul>";
        echo "<p><a href='admin/students.php'>View Students in Admin Panel</a></p>";

    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creating test data: " . $e->getMessage() . "</p>";
    }
}

// Check current data
$user_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
$user_count_stmt = $db->prepare($user_count_query);
$user_count_stmt->execute();
$user_count = $user_count_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$enrollment_count_query = "SELECT COUNT(*) as count FROM enrollments";
$enrollment_count_stmt = $db->prepare($enrollment_count_query);
$enrollment_count_stmt->execute();
$enrollment_count = $enrollment_count_stmt->fetch(PDO::FETCH_ASSOC)['count'];

echo "<h3>Current Data Status:</h3>";
echo "<p>Student Users: <strong>$user_count</strong></p>";
echo "<p>Enrollments: <strong>$enrollment_count</strong></p>";

if ($enrollment_count == 0) {
    echo "<form method='POST'>";
    echo "<p>No enrollment data found. Would you like to create some test data?</p>";
    echo "<button type='submit' name='create_test_data' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Create Test Data</button>";
    echo "</form>";
} else {
    echo "<p style='color: green;'>✅ Enrollment data exists. <a href='admin/students.php'>View in Admin Panel</a></p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
button:hover { background: #0056b3 !important; }
</style>
