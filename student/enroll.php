<?php
require_once '../config/database.php';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Handle photo upload
        $photo_path = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $uploaded_path = uploadFile($_FILES['photo'], '../uploads/photos/');
            // Store relative path from root for consistent access
            $photo_path = str_replace('../', '', $uploaded_path);
        } elseif ($existing_enrollment && !empty($existing_enrollment['photo_path'])) {
            $photo_path = $existing_enrollment['photo_path'];
        }

        if ($existing_enrollment) {
            // Update existing enrollment (resubmission)
            if (empty($photo_path)) {
                throw new Exception('Please upload a photo before resubmitting.');
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
            $required_fields = [
                'first_name', 'middle_name', 'last_name', 'age', 'date_of_birth', 
                'place_of_birth', 'height', 'weight', 'block', 'course', 'army_nstp',
                'address', 'phone', 'email', 'emergency_contact_person', 
                'relationship', 'emergency_contact_number'
            ];
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception('Please fill in all required fields.');
                }
            }
            
            if (empty($photo_path)) {
                throw new Exception('Please upload a photo.');
            }
            
            $insert_query = "INSERT INTO enrollments (
                student_id, first_name, middle_name, last_name, age, religion, 
                date_of_birth, place_of_birth, height, weight, complexion, blood_type,
                block, course, army_nstp, address, phone, email,
                father_name, father_occupation, mother_name, mother_occupation,
                emergency_contact_person, relationship, emergency_contact_number,
                ms, semester, school_year, grade, ms_remarks, advance_course,
                photo_path, status, submitted_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', NOW())";
            
            $stmt = $db->prepare($insert_query);
            $stmt->execute([
                $student_id,
                sanitizeInput($_POST['first_name']),
                sanitizeInput($_POST['middle_name']),
                sanitizeInput($_POST['last_name']),
                (int)$_POST['age'],
                sanitizeInput($_POST['religion']),
                $_POST['date_of_birth'],
                sanitizeInput($_POST['place_of_birth']),
                (float)$_POST['height'],
                (float)$_POST['weight'],
                sanitizeInput($_POST['complexion']),
                sanitizeInput($_POST['blood_type']),
                sanitizeInput($_POST['block']),
                sanitizeInput($_POST['course']),
                sanitizeInput($_POST['army_nstp']),
                sanitizeInput($_POST['address']),
                sanitizeInput($_POST['phone']),
                sanitizeInput($_POST['email']),
                sanitizeInput($_POST['father_name']),
                sanitizeInput($_POST['father_occupation']),
                sanitizeInput($_POST['mother_name']),
                sanitizeInput($_POST['mother_occupation']),
                sanitizeInput($_POST['emergency_contact_person']),
                sanitizeInput($_POST['relationship']),
                sanitizeInput($_POST['emergency_contact_number']),
                sanitizeInput($_POST['ms']),
                sanitizeInput($_POST['semester']),
                sanitizeInput($_POST['school_year']),
                sanitizeInput($_POST['grade']),
                sanitizeInput($_POST['ms_remarks']),
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

$is_resubmission = $existing_enrollment && in_array($existing_enrollment['status'], ['submitted', 'resubmitted', 'rejected']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ROTC Enrollment Form - Student Portal</title>
  <link rel="stylesheet" href="../assets/css/enroll.css">
  <link rel="stylesheet" href="../assets/css/green-theme.css">
  <style>
    .navbar {
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 15px rgba(0,0,0,0.15);
      margin-bottom: 0;
      position: sticky;
      top: 0;
      z-index: 1000;
      min-height: 70px;
    }
    
    .navbar-brand {
      font-size: 1.5rem;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .navbar-logo {
      height: 45px !important;
      width: auto !important;
      max-height: 45px !important;
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      background: white;
      padding: 4px;
    }
    
    /* Mobile navbar adjustments */
    @media (max-width: 768px) {
      .navbar {
        padding: 0.8rem 1rem;
      }
      
      .navbar-brand {
        font-size: 1.2rem;
        gap: 10px;
      }
      
      .navbar-logo {
        height: 35px !important;
        max-height: 35px !important;
      }
      
      
      .navbar-brand > div > div:first-child {
        font-size: 1rem !important;
      }
      
      .navbar-brand > div > div:last-child {
        font-size: 0.7rem !important;
      }
      
      .navbar-nav {
        gap: 15px;
      }
      
      .nav-link {
        padding: 6px 12px;
        font-size: 0.9rem;
      }
    }
    
    .navbar-nav {
      display: flex;
      gap: 20px;
      align-items: center;
    }
    
    .nav-link {
      color: white;
      text-decoration: none;
      padding: 8px 16px;
      border-radius: 20px;
      transition: background-color 0.3s;
    }
    
    .nav-link:hover {
      background-color: rgba(255,255,255,0.2);
    }
    
    /* Page layout structure */
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    
    .main-content {
      flex: 1;
      padding: 2rem 0;
    }
    
    .enroll-container {
      margin-top: 0 !important;
    }
    
    /* Header logo removed - no longer needed */
    
    /* Footer styling - using green theme */
    .footer p {
      margin: 0.5rem 0;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <!-- Navigation Header -->
  <nav class="navbar">
    <div class="navbar-brand">
      <img src="../assets/img/jhcsc-logo.png" alt="JHCSC Logo" class="navbar-logo">
      <div>
        <div style="font-size: 1.2rem;">Student Portal</div>
        <div style="font-size: 0.8rem; opacity: 0.9;">Digital ROTC System</div>
      </div>
    </div>
    <div class="navbar-nav">
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="profile.php" class="nav-link">Profile</a>
      <a href="../index.php" class="nav-link">Logout</a>
    </div>
  </nav>

  <!-- Main Content Area -->
  <main class="main-content">
    <div class="enroll-container">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <div class="header-text">
            <h2>ROTC Enrollment Form</h2>
            <p class="page-subtitle">Jose Hernandez College of Science and Computing</p>
            <p class="page-subtitle">Complete all required fields to submit your enrollment application</p>
          </div>
        </div>
      </div>
    
    <?php if (!empty($error_message)): ?>
      <div class="error-message" style="color: red; margin-bottom: 15px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;">
        <?php echo htmlspecialchars($error_message); ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
      <div class="success-message" style="color: green; margin-bottom: 15px; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">
        <?php echo htmlspecialchars($success_message); ?>
        <br><a href="dashboard.php">Return to Dashboard</a>
      </div>
    <?php endif; ?>

    <?php if ($is_resubmission): ?>
      <div class="info-message" style="color: #856404; margin-bottom: 15px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
        <strong>Resubmission Mode:</strong> You can only update your photo. Other fields are locked.
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <!-- Personal Information Section -->
      <fieldset>
        <legend>Personal Information</legend>
        
        <div class="form-grid">
          <label>First Name
            <input type="text" name="first_name" placeholder="First Name" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['first_name']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Middle Name
            <input type="text" name="middle_name" placeholder="Middle Name" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['middle_name']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Surname
            <input type="text" name="last_name" placeholder="Last Name" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['last_name']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Age
            <input type="number" name="age" placeholder="Age" 
                   value="<?php echo $existing_enrollment ? $existing_enrollment['age'] : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Religion
            <input type="text" name="religion" placeholder="Religion"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['religion']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>

          <label>Date of Birth
            <input type="date" name="date_of_birth" 
                   value="<?php echo $existing_enrollment ? $existing_enrollment['date_of_birth'] : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Place of Birth
            <input type="text" name="place_of_birth" placeholder="Place of Birth" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['place_of_birth']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Height (cm)
            <input type="number" name="height" placeholder="Height" step="0.01"
                   value="<?php echo $existing_enrollment ? $existing_enrollment['height'] : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Weight (kg)
            <input type="number" name="weight" placeholder="Weight" step="0.01"
                   value="<?php echo $existing_enrollment ? $existing_enrollment['weight'] : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Complexion
            <input type="text" name="complexion" placeholder="Complexion"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['complexion']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>

          <label>Blood Type
            <select name="blood_type" <?php echo $is_resubmission ? 'disabled' : 'required'; ?>>
              <option value="">--Select Blood Type--</option>
              <option value="A" <?php echo ($existing_enrollment && $existing_enrollment['blood_type'] == 'A') ? 'selected' : ''; ?>>Type A</option>
              <option value="B" <?php echo ($existing_enrollment && $existing_enrollment['blood_type'] == 'B') ? 'selected' : ''; ?>>Type B</option>
              <option value="AB" <?php echo ($existing_enrollment && $existing_enrollment['blood_type'] == 'AB') ? 'selected' : ''; ?>>Type AB</option>
              <option value="O" <?php echo ($existing_enrollment && $existing_enrollment['blood_type'] == 'O') ? 'selected' : ''; ?>>Type O</option>
            </select>
          </label>
        </div>
      </fieldset>

      <!-- Academic Information Section -->
      <fieldset>
        <legend>Academic Information</legend>
        
        <div class="form-grid">
          <label>Block
            <select name="block" <?php echo $is_resubmission ? 'disabled' : 'required'; ?>>
              <option value="">-- Select Block --</option>
              <option value="A" <?php echo ($existing_enrollment && $existing_enrollment['block'] == 'A') ? 'selected' : ''; ?>>Block A</option>
              <option value="B" <?php echo ($existing_enrollment && $existing_enrollment['block'] == 'B') ? 'selected' : ''; ?>>Block B</option>
              <option value="C" <?php echo ($existing_enrollment && $existing_enrollment['block'] == 'C') ? 'selected' : ''; ?>>Block C</option>
            </select>
          </label>

          <label>Course
            <select name="course" <?php echo $is_resubmission ? 'disabled' : 'required'; ?>>
              <option value="">-- Select Course --</option>
              <option value="BSIT" <?php echo ($existing_enrollment && $existing_enrollment['course'] == 'BSIT') ? 'selected' : ''; ?>>BSIT</option>
              <option value="NURSING" <?php echo ($existing_enrollment && $existing_enrollment['course'] == 'NURSING') ? 'selected' : ''; ?>>NURSING</option>
              <option value="BSHM" <?php echo ($existing_enrollment && $existing_enrollment['course'] == 'BSHM') ? 'selected' : ''; ?>>BSHM</option>
              <option value="TOURISM" <?php echo ($existing_enrollment && $existing_enrollment['course'] == 'TOURISM') ? 'selected' : ''; ?>>TOURISM</option>
              <option value="EDUC" <?php echo ($existing_enrollment && $existing_enrollment['course'] == 'EDUC') ? 'selected' : ''; ?>>EDUC</option>
            </select>
          </label>

          <label>Army NSTP
            <select name="army_nstp" <?php echo $is_resubmission ? 'disabled' : 'required'; ?>>
              <option value="">-- Select NSTP Program --</option>
              <option value="ROTC" <?php echo ($existing_enrollment && $existing_enrollment['army_nstp'] == 'ROTC') ? 'selected' : ''; ?>>NSTP - ROTC</option>
              <option value="CWTS" <?php echo ($existing_enrollment && $existing_enrollment['army_nstp'] == 'CWTS') ? 'selected' : ''; ?>>NSTP - CWTS</option>
              <option value="LTS" <?php echo ($existing_enrollment && $existing_enrollment['army_nstp'] == 'LTS') ? 'selected' : ''; ?>>NSTP - LTS</option>
            </select>
          </label>
        </div>
      </fieldset>

      <!-- Contact Information Section -->
      <fieldset>
        <legend>Contact Information</legend>
        
        <div class="form-grid">
          <label style="grid-column: 1 / -1;">Address
            <input type="text" name="address" placeholder="Complete Address" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['address']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Phone/Cell Number
            <input type="tel" name="phone" placeholder="09XXXXXXXXX" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['phone']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Email
            <input type="email" name="email" placeholder="Email" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['email']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>
        </div>
      </fieldset>

      <!-- Parent/Guardian Info -->
      <fieldset>
        <legend>Parent/Guardian Information</legend>

        <div class="form-grid">
          <label>Father's Name
            <input type="text" name="father_name" placeholder="Father's Name"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['father_name']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>

          <label>Father's Occupation
            <input type="text" name="father_occupation" placeholder="Occupation"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['father_occupation']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>

          <label>Mother's Name
            <input type="text" name="mother_name" placeholder="Mother's Name"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['mother_name']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>

          <label>Mother's Occupation
            <input type="text" name="mother_occupation" placeholder="Occupation"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['mother_occupation']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>

          <label>Emergency Contact Person
            <input type="text" name="emergency_contact_person" placeholder="Full Name" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['emergency_contact_person']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Relationship
            <input type="text" name="relationship" placeholder="Relationship" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['relationship']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>

          <label>Emergency Contact Number
            <input type="tel" name="emergency_contact_number" placeholder="09XXXXXXXXX" 
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['emergency_contact_number']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : 'required'; ?>>
          </label>
        </div>
      </fieldset>

      <!-- Military Science Section -->
      <fieldset>
        <legend>Military Science Completed</legend>
        
        <div class="form-grid">
          <label>MS
            <input type="text" name="ms" placeholder="MS"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['ms']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>
          
          <label>Semester
            <input type="text" name="semester" placeholder="Semester"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['semester']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>
          
          <label>School Year
            <input type="text" name="school_year" placeholder="YYYY-YYYY"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['school_year']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>
          
          <label>Grade
            <input type="text" name="grade" placeholder="Grade"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['grade']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>
          
          <label style="grid-column: 1 / -1;">Remarks
            <input type="text" name="ms_remarks" placeholder="Remarks"
                   value="<?php echo $existing_enrollment ? htmlspecialchars($existing_enrollment['ms_remarks']) : ''; ?>"
                   <?php echo $is_resubmission ? 'readonly' : ''; ?>>
          </label>
        </div>
      </fieldset>

      <!-- Additional Information Section -->
      <fieldset>
        <legend>Additional Information</legend>
        
        <label>
          <input type="checkbox" name="advance_course" 
                 <?php echo ($existing_enrollment && $existing_enrollment['advance_course']) ? 'checked' : ''; ?>
                 <?php echo $is_resubmission ? 'disabled' : ''; ?>> 
          I am willing to take the Cadet Officer Qualifying/Advance Course
        </label>
      </fieldset>

      <!-- Photo Upload Section -->
      <fieldset>
        <legend>Photo Upload</legend>
        
        <label>2x2 Photo <?php echo $is_resubmission ? '(Update Photo)' : ''; ?>
          <input type="file" name="photo" accept="image/*" <?php echo !$is_resubmission ? 'required' : ''; ?>>
          <?php if ($existing_enrollment && !empty($existing_enrollment['photo_path'])): ?>
            <div class="photo-preview">
              <img src="<?php echo htmlspecialchars($existing_enrollment['photo_path']); ?>" 
                   alt="Current Photo">
              <br><small>Current Photo</small>
            </div>
          <?php endif; ?>
        </label>
      </fieldset>

      <!-- Submit Buttons -->
      <div class="button-container">
        <button type="submit">
          <?php echo $is_resubmission ? 'Resubmit Form' : 'Submit Enrollment'; ?>
        </button>
        
        <button type="button" onclick="window.location.href='dashboard.php'" style="background-color: #6c757d;">
          Back to Dashboard
        </button>
      </div>
    </form>
    </div>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <p><strong>Digital ROTC System</strong></p>
    <p>Jose Hernandez College of Science and Computing</p>
    <p>&copy; 2024 All Rights Reserved | For technical support, contact the IT Department</p>
  </footer>
</body>
</html>
