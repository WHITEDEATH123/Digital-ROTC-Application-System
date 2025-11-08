<?php
require_once 'config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('student');

$database = new Database();
$db = $database->getConnection();

$student_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Get existing enrollment data
$query = "SELECT * FROM enrollments WHERE student_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([$student_id]);
$existing_enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

// Sanitize input function
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle photo upload
        $photo_path = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $photo_path = uploadFile($_FILES['photo']);
            if (!$photo_path) throw new Exception("Photo upload failed.");
        } elseif ($existing_enrollment && !empty($existing_enrollment['photo_path'])) {
            $photo_path = $existing_enrollment['photo_path'];
        }

        // Collect and sanitize fields
        $fields = [
            'first_name','middle_name','last_name','age','religion','date_of_birth',
            'place_of_birth','height','weight','complexion','blood_type','block','course',
            'army_nstp','address','phone','email','father_name','father_occupation','mother_name',
            'mother_occupation','emergency_contact_person','relationship','emergency_contact_number',
            'ms','semester','school_year','grade','ms_remarks','advance_course'
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = isset($_POST[$field]) ? sanitizeInput($_POST[$field]) : null;
        }

        // Required fields validation
        $required_fields = [
            'first_name','middle_name','last_name','age','date_of_birth','place_of_birth',
            'height','weight','block','course','army_nstp','address','phone','email',
            'emergency_contact_person','relationship','emergency_contact_number'
        ];

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Please fill in all required fields. Missing: $field");
            }
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address provided.");
        }
        // Update users.email in the users table
$stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
$stmt->execute([$data['email'], $student_id]);


        // Update users.email
        $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$data['email'], $student_id]);

        if ($existing_enrollment) {
            // Resubmission: only photo is updated
            if (empty($photo_path)) {
                throw new Exception("Please upload a photo before resubmitting.");
            }

            $update_query = "UPDATE enrollments SET 
                photo_path = ?, 
                status = 'resubmitted', 
                admin_remarks = '', 
                resubmitted_at = NOW(),
                updated_at = NOW()
                WHERE student_id = ?";
            $stmt = $db->prepare($update_query);
            $stmt->execute([$photo_path, $student_id]);

            $success_message = 'Form resubmitted successfully!';
        } else {
            // New enrollment
            $insert_query = "INSERT INTO enrollments (
                student_id, first_name, middle_name, last_name, age, religion,
                date_of_birth, place_of_birth, height, weight, complexion, blood_type,
                block, course, army_nstp, address, phone, email,
                father_name, father_occupation, mother_name, mother_occupation,
                emergency_contact_person, relationship, emergency_contact_number,
                ms, semester, school_year, grade, ms_remarks, advance_course,
                photo_path, status, submitted_at, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', NOW(), NOW()
            )";

            $stmt = $db->prepare($insert_query);
            $stmt->execute([
                $student_id,
                $data['first_name'],$data['middle_name'],$data['last_name'],(int)$data['age'],$data['religion'],
                $data['date_of_birth'],$data['place_of_birth'],(float)$data['height'],(float)$data['weight'],$data['complexion'],$data['blood_type'],
                $data['block'],$data['course'],$data['army_nstp'],$data['address'],$data['phone'],$data['email'],
                $data['father_name'],$data['father_occupation'],$data['mother_name'],$data['mother_occupation'],
                $data['emergency_contact_person'],$data['relationship'],$data['emergency_contact_number'],
                $data['ms'],$data['semester'],$data['school_year'],$data['grade'],$data['ms_remarks'],
                isset($_POST['advance_course']) ? 1 : 0,
                $photo_path
            ]);

            $success_message = 'Enrollment form submitted successfully!';
        }

        // Refresh enrollment data
        $stmt = $db->prepare($query);
        $stmt->execute([$student_id]);
        $existing_enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Determine if resubmission mode
$is_resubmission = $existing_enrollment && in_array($existing_enrollment['status'], ['submitted','resubmitted','rejected']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ROTC Enrollment Form</title>
<link rel="stylesheet" href="assets/css/enroll.css">
</head>
<body>
<div class="enroll-container">
<h2>ROTC Enrollment Form</h2>

<?php if (!empty($error_message)): ?>
    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="success-message"><?php echo htmlspecialchars($success_message); ?><br><a href="dashboard.php">Return to Dashboard</a></div>
<?php endif; ?>

<?php if ($is_resubmission): ?>
    <div class="info-message">Resubmission Mode: Only photo can be updated.</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <!-- Example: First Name -->
    <label>First Name
        <input type="text" name="first_name" value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['first_name']) : ''; ?>" <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
    </label>

    <!-- Middle Name -->
    <label>Middle Name
        <input type="text" name="middle_name" value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['middle_name']) : ''; ?>" <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
    </label>

    <!-- Last Name -->
    <label>Surname
        <input type="text" name="last_name" value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['last_name']) : ''; ?>" <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
    </label>

    <!-- Email -->
    <label>Email
        <input type="email" name="email" value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['email']) : ''; ?>" <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
    </label>

    <!-- Photo -->
    <label>2x2 Photo <?php echo $is_resubmission ? '(Update Photo)' : ''; ?>
        <input type="file" name="photo" accept="image/*" <?php echo !$is_resubmission ? 'required' : ''; ?>>
        <?php if ($existing_enrollment && !empty($existing_enrollment['photo_path'])): ?>
            <img src="<?php echo htmlspecialchars($existing_enrollment['photo_path']); ?>" style="max-width:100px;">
        <?php endif; ?>
    </label>

    <!-- Add other fields similarly… -->

    <button type="submit"><?php echo $is_resubmission ? 'Resubmit Form' : 'Submit Enrollment'; ?></button>
    <button type="button" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
</form>
</div>
</body>
</html>
