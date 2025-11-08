<?php
require_once 'config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('student');

$database = new Database();
$db = $database->getConnection();

$student_id = $_SESSION['user_id'];

// Get enrollment information
$query = "SELECT * FROM enrollments WHERE student_id = ? AND status = 'approved' ORDER BY created_at DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([$student_id]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enrollment) {
    header('Location: dashboard.php?error=no_approved_enrollment');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cadet Enrollment Form - Print View</title>
  <link rel="stylesheet" href="assets/css/cadetview.css">
  <style>
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; }
      .container { box-shadow: none; margin: 0; }
    }
    
    .print-header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 2px solid #333;
      padding-bottom: 20px;
    }
    
    .print-header h1 {
      margin: 0;
      font-size: 24px;
      color: #333;
    }
    
    .print-header p {
      margin: 5px 0;
      color: #666;
    }
    
    .form-section {
      margin-bottom: 25px;
      page-break-inside: avoid;
    }
    
    .form-row {
      display: flex;
      gap: 20px;
      margin-bottom: 15px;
    }
    
    .form-row label {
      flex: 1;
      display: block;
    }
    
    .form-row input {
      width: 100%;
      border: none;
      border-bottom: 1px solid #333;
      padding: 5px 0;
      background: transparent;
    }
    
    .photo-section {
      text-align: center;
      margin: 20px 0;
    }
    
    .photo-section img {
      max-width: 150px;
      max-height: 150px;
      border: 2px solid #333;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Print Header -->
    <div class="print-header">
      <h1>ROTC ENROLLMENT FORM</h1>
      <p>Jose Hernandez College of Science and Computing</p>
      <p>Digital ROTC Application System</p>
      <p><strong>Student ID:</strong> <?php echo htmlspecialchars($enrollment['student_id']); ?></p>
      <p><strong>Date Approved:</strong> <?php echo date('F j, Y', strtotime($enrollment['reviewed_at'] ?: $enrollment['updated_at'])); ?></p>
    </div>

    <form id="cadetForm">
      <!-- Student Information -->
      <fieldset class="form-section">
        <legend><strong>STUDENT INFORMATION</strong></legend>
        <div class="form-row">
          <label><strong>First Name:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['first_name']); ?>" readonly>
          </label>
          <label><strong>Middle Name:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['middle_name']); ?>" readonly>
          </label>
          <label><strong>Surname:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['last_name']); ?>" readonly>
          </label>
        </div>
        
        <div class="form-row">
          <label><strong>Age:</strong> 
            <input type="text" value="<?php echo $enrollment['age']; ?>" readonly>
          </label>
          <label><strong>Religion:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['religion']); ?>" readonly>
          </label>
          <label><strong>Date of Birth:</strong> 
            <input type="text" value="<?php echo date('F j, Y', strtotime($enrollment['date_of_birth'])); ?>" readonly>
          </label>
        </div>
        
        <div class="form-row">
          <label><strong>Place of Birth:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['place_of_birth']); ?>" readonly>
          </label>
          <label><strong>Height (cm):</strong> 
            <input type="text" value="<?php echo $enrollment['height']; ?>" readonly>
          </label>
          <label><strong>Weight (kg):</strong> 
            <input type="text" value="<?php echo $enrollment['weight']; ?>" readonly>
          </label>
        </div>
        
        <div class="form-row">
          <label><strong>Complexion:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['complexion']); ?>" readonly>
          </label>
          <label><strong>Blood Type:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['blood_type']); ?>" readonly>
          </label>
        </div>
      </fieldset>

      <!-- Academic Information -->
      <fieldset class="form-section">
        <legend><strong>ACADEMIC INFORMATION</strong></legend>
        <div class="form-row">
          <label><strong>Block:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['block']); ?>" readonly>
          </label>
          <label><strong>Course:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['course']); ?>" readonly>
          </label>
          <label><strong>Army NSTP:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['army_nstp']); ?>" readonly>
          </label>
        </div>
      </fieldset>

      <!-- Contact Information -->
      <fieldset class="form-section">
        <legend><strong>CONTACT INFORMATION</strong></legend>
        <label><strong>Address:</strong> 
          <input type="text" value="<?php echo htmlspecialchars($enrollment['address']); ?>" readonly>
        </label>
        <div class="form-row">
          <label><strong>Phone/Cell Number:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['phone']); ?>" readonly>
          </label>
          <label><strong>Email:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['email']); ?>" readonly>
          </label>
        </div>
      </fieldset>

      <!-- Parent/Guardian Information -->
      <fieldset class="form-section">
        <legend><strong>PARENT/GUARDIAN INFORMATION</strong></legend>
        <div class="form-row">
          <label><strong>Father's Name:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['father_name']); ?>" readonly>
          </label>
          <label><strong>Father's Occupation:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['father_occupation']); ?>" readonly>
          </label>
        </div>
        
        <div class="form-row">
          <label><strong>Mother's Name:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['mother_name']); ?>" readonly>
          </label>
          <label><strong>Mother's Occupation:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['mother_occupation']); ?>" readonly>
          </label>
        </div>
        
        <div class="form-row">
          <label><strong>Emergency Contact Person:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['emergency_contact_person']); ?>" readonly>
          </label>
          <label><strong>Relationship:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['relationship']); ?>" readonly>
          </label>
          <label><strong>Emergency Contact Number:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['emergency_contact_number']); ?>" readonly>
          </label>
        </div>
      </fieldset>

      <!-- Military Science Information -->
      <fieldset class="form-section">
        <legend><strong>MILITARY SCIENCE COMPLETED</strong></legend>
        <div class="form-row">
          <label><strong>MS:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['ms']); ?>" readonly>
          </label>
          <label><strong>Semester:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['semester']); ?>" readonly>
          </label>
          <label><strong>School Year:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['school_year']); ?>" readonly>
          </label>
        </div>
        
        <div class="form-row">
          <label><strong>Grade:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['grade']); ?>" readonly>
          </label>
          <label><strong>Remarks:</strong> 
            <input type="text" value="<?php echo htmlspecialchars($enrollment['ms_remarks']); ?>" readonly>
          </label>
        </div>
      </fieldset>

      <!-- Advance Course -->
      <fieldset class="form-section">
        <legend><strong>ADVANCE COURSE</strong></legend>
        <label>
          <input type="checkbox" <?php echo $enrollment['advance_course'] ? 'checked' : ''; ?> disabled> 
          <strong>Willing to take Cadet Officer Qualifying/Advance Course</strong>
        </label>
      </fieldset>

      <!-- Photo Section -->
      <fieldset class="form-section">
        <legend><strong>2x2 PHOTO</strong></legend>
        <div class="photo-section">
          <?php if (!empty($enrollment['photo_path']) && file_exists($enrollment['photo_path'])): ?>
            <img src="<?php echo htmlspecialchars($enrollment['photo_path']); ?>" alt="Cadet Photo">
          <?php else: ?>
            <div style="width: 150px; height: 150px; border: 2px solid #333; display: inline-block; line-height: 150px; text-align: center; color: #666;">
              No Photo
            </div>
          <?php endif; ?>
        </div>
      </fieldset>

      <!-- Admin Remarks -->
      <?php if (!empty($enrollment['admin_remarks'])): ?>
      <fieldset class="form-section">
        <legend><strong>ADMIN REMARKS</strong></legend>
        <div style="padding: 10px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
          <?php echo nl2br(htmlspecialchars($enrollment['admin_remarks'])); ?>
        </div>
      </fieldset>
      <?php endif; ?>

      <!-- Signature Section -->
      <fieldset class="form-section">
        <legend><strong>SIGNATURES</strong></legend>
        <div style="display: flex; justify-content: space-between; margin-top: 50px;">
          <div style="text-align: center; width: 200px;">
            <div style="border-bottom: 1px solid #333; height: 50px;"></div>
            <p><strong>Student Signature</strong></p>
            <p>Date: _______________</p>
          </div>
          
          <div style="text-align: center; width: 200px;">
            <div style="border-bottom: 1px solid #333; height: 50px;"></div>
            <p><strong>Admin Signature</strong></p>
            <p>Date: <?php echo date('F j, Y'); ?></p>
          </div>
        </div>
      </fieldset>

      <!-- Print Button -->
      <div class="actions no-print" style="text-align: center; margin-top: 30px;">
        <button type="button" onclick="window.print()" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
          🖨️ Print Form
        </button>
        <button type="button" onclick="window.location.href='dashboard.php'" style="background-color: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
          ← Back to Dashboard
        </button>
      </div>
    </form>
  </div>
</body>
</html>
