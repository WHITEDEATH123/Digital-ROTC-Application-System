  <?php

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  require_once 'config/database.php';
  require_once __DIR__ . '/gmail/send_gmail.php';

  $sessionManager = new SessionManager();
  $sessionManager->requireRole('admin');

  $database = new Database();
  $db = $database->getConnection();

  $enrollment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $message = '';
  $error_message = '';

  if (!$enrollment_id) {
      header('Location: admin.php');
      exit();
  }


  // Get enrollment details
$query = "SELECT e.*, e.email as student_email
          FROM enrollments e
          WHERE e.id = ?";

  $stmt = $db->prepare($query);
  $stmt->execute([$enrollment_id]);
  $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$enrollment) {
      header('Location: admin.php');
      exit();
  }

  // Cadet email and full name
$cadetEmail = trim($enrollment['email']); // email from enrollments table
$fullName = $enrollment['first_name'] . ' ' . $enrollment['last_name'];

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Make sure the cadet email is set
        $cadetEmail = trim($enrollment['email']); // from enrollments table
        $fullName = $enrollment['first_name'] . ' ' . $enrollment['last_name'];

        if (empty($cadetEmail) || !filter_var($cadetEmail, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Cadet email is invalid or empty. Email not sent.";
        } else {

            // APPROVE
            if (isset($_POST['approve'])) {
                $stmt = $db->prepare("UPDATE enrollments SET status = 'approved', reviewed_at = NOW() WHERE id = ?");
                $stmt->execute([$enrollment_id]);
                $enrollment['status'] = 'approved';
                $message = 'Enrollment approved successfully!';

                $subject = "Your ROTC Enrollment has been Approved";
                $body = "Hello {$fullName},<br><br>Your ROTC enrollment has been <b>approved</b>.<br>Thank you,<br>ROTC Admin";
                $result = sendGmail($cadetEmail, $subject, $body);
                if ($result !== true) $error_message = 'Error sending email: ' . $result;
            }

            // REJECT
            if (isset($_POST['reject'])) {
                $stmt = $db->prepare("UPDATE enrollments SET status = 'rejected', reviewed_at = NOW() WHERE id = ?");
                $stmt->execute([$enrollment_id]);
                $enrollment['status'] = 'rejected';
                $message = 'Enrollment rejected successfully!';

                $subject = "Your ROTC Enrollment has been Rejected";
                $body = "Hello {$fullName},<br><br>Unfortunately, your ROTC enrollment has been <b>rejected</b>.<br>Please contact the administrator for details.<br>ROTC Admin";
                $result = sendGmail($cadetEmail, $subject, $body);
                if ($result !== true) $error_message = 'Error sending email: ' . $result;
            }

            // SEND REMARKS
            if (isset($_POST['send_remarks'])) {
                $remarks = sanitizeInput($_POST['admin_remarks']);
                $stmt = $db->prepare("UPDATE enrollments SET admin_remarks = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$remarks, $enrollment_id]);
                $enrollment['admin_remarks'] = $remarks;
                $message = 'Remarks sent successfully!';

                $subject = "Remarks from ROTC Administrator";
                $body = "Hello {$fullName},<br><br>You have received new remarks from the administrator:<br><blockquote>{$remarks}</blockquote><br>Please review and respond if needed.<br>ROTC Admin";
                $result = sendGmail($cadetEmail, $subject, $body);
                if ($result !== true) $error_message = 'Error sending email: ' . $result;
            }

        }

    } catch (Exception $e) {
        $error_message = 'Error: ' . $e->getMessage();
    }
}


  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadet Details - <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></title>
    <link rel="stylesheet" href="assets/css/view.css">
  </head>
  <body>
    <div class="view-container">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Cadet Enrollment Details</h2>
        <button onclick="window.location.href='admin.php'" class="btn-back">← Back to Admin Panel</button>
      </div>

      <?php if (!empty($message)): ?>
        <div class="success-message" style="color: green; margin-bottom: 15px; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($error_message)): ?>
        <div class="error-message" style="color: red; margin-bottom: 15px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <div class="status-info" style="margin-bottom: 20px; padding: 10px; border-radius: 5px; background-color: #f8f9fa;">
        <strong>Status:</strong> 
        <span class="status-badge status-<?php echo $enrollment['status']; ?>">
          <?php echo ucfirst($enrollment['status']); ?>
        </span>
        <br>
        <strong>Student ID:</strong> <?php echo htmlspecialchars($enrollment['student_id']); ?>
        <br>
        <strong>Submitted:</strong> <?php echo date('F j, Y g:i A', strtotime($enrollment['submitted_at'] ?: $enrollment['created_at'])); ?>
        <?php if ($enrollment['resubmitted_at']): ?>
          <br><strong>Resubmitted:</strong> <?php echo date('F j, Y g:i A', strtotime($enrollment['resubmitted_at'])); ?>
        <?php endif; ?>
      </div>

      <form method="POST">
        <!-- Student Info -->
        <fieldset>
          <legend>Personal Information</legend>
          <div class="form-row">
            <label>First Name
              <input type="text" value="<?php echo htmlspecialchars($enrollment['first_name']); ?>" readonly>
            </label>
            <label>Middle Name
              <input type="text" value="<?php echo htmlspecialchars($enrollment['middle_name']); ?>" readonly>
            </label>
            <label>Surname
              <input type="text" value="<?php echo htmlspecialchars($enrollment['last_name']); ?>" readonly>
            </label>
          </div>
          
          <div class="form-row">
            <label>Age
              <input type="number" value="<?php echo $enrollment['age']; ?>" readonly>
            </label>
            <label>Religion
              <input type="text" value="<?php echo htmlspecialchars($enrollment['religion']); ?>" readonly>
            </label>
            <label>Date of Birth
              <input type="date" value="<?php echo $enrollment['date_of_birth']; ?>" readonly>
            </label>
          </div>
          
          <div class="form-row">
            <label>Place of Birth
              <input type="text" value="<?php echo htmlspecialchars($enrollment['place_of_birth']); ?>" readonly>
            </label>
            <label>Height (cm)
              <input type="number" value="<?php echo $enrollment['height']; ?>" readonly>
            </label>
            <label>Weight (kg)
              <input type="number" value="<?php echo $enrollment['weight']; ?>" readonly>
            </label>
          </div>
          
          <div class="form-row">
            <label>Complexion
              <input type="text" value="<?php echo htmlspecialchars($enrollment['complexion']); ?>" readonly>
            </label>
            <label>Blood Type
              <input type="text" value="<?php echo htmlspecialchars($enrollment['blood_type']); ?>" readonly>
            </label>
          </div>
        </fieldset>

        <!-- Academic Info -->
        <fieldset>
          <legend>Academic Information</legend>
          <div class="form-row">
            <label>Block
              <input type="text" value="<?php echo htmlspecialchars($enrollment['block']); ?>" readonly>
            </label>
            <label>Course
              <input type="text" value="<?php echo htmlspecialchars($enrollment['course']); ?>" readonly>
            </label>
            <label>Army NSTP
              <input type="text" value="<?php echo htmlspecialchars($enrollment['army_nstp']); ?>" readonly>
            </label>
          </div>
        </fieldset>

        <!-- Contact Info -->
        <fieldset>
          <legend>Contact Information</legend>
          <label>Address
            <textarea readonly><?php echo htmlspecialchars($enrollment['address']); ?></textarea>
          </label>
          <div class="form-row">
            <label>Phone/Cell Number
              <input type="tel" value="<?php echo htmlspecialchars($enrollment['phone']); ?>" readonly>
            </label>
            <label>Email
              <input type="email" value="<?php echo htmlspecialchars($enrollment['email']); ?>" readonly>
            </label>
          </div>
        </fieldset>

        <!-- Parent/Guardian Info -->
        <fieldset>
          <legend>Parent/Guardian Information</legend>
          <div class="form-row">
            <label>Father's Name
              <input type="text" value="<?php echo htmlspecialchars($enrollment['father_name']); ?>" readonly>
            </label>
            <label>Father's Occupation
              <input type="text" value="<?php echo htmlspecialchars($enrollment['father_occupation']); ?>" readonly>
            </label>
          </div>
          
          <div class="form-row">
            <label>Mother's Name
              <input type="text" value="<?php echo htmlspecialchars($enrollment['mother_name']); ?>" readonly>
            </label>
            <label>Mother's Occupation
              <input type="text" value="<?php echo htmlspecialchars($enrollment['mother_occupation']); ?>" readonly>
            </label>
          </div>
          
          <div class="form-row">
            <label>Emergency Contact Person
              <input type="text" value="<?php echo htmlspecialchars($enrollment['emergency_contact_person']); ?>" readonly>
            </label>
            <label>Relationship
              <input type="text" value="<?php echo htmlspecialchars($enrollment['relationship']); ?>" readonly>
            </label>
            <label>Emergency Contact Number
              <input type="tel" value="<?php echo htmlspecialchars($enrollment['emergency_contact_number']); ?>" readonly>
            </label>
          </div>
        </fieldset>

        <!-- Military Science Section -->
        <fieldset>
          <legend>Military Science Completed</legend>
          <div class="form-row">
            <label>MS
              <input type="text" value="<?php echo htmlspecialchars($enrollment['ms']); ?>" readonly>
            </label>
            <label>Semester
              <input type="text" value="<?php echo htmlspecialchars($enrollment['semester']); ?>" readonly>
            </label>
            <label>School Year
              <input type="text" value="<?php echo htmlspecialchars($enrollment['school_year']); ?>" readonly>
            </label>
          </div>
          <div class="form-row">
            <label>Grade
              <input type="text" value="<?php echo htmlspecialchars($enrollment['grade']); ?>" readonly>
            </label>
            <label>MS Remarks
              <input type="text" value="<?php echo htmlspecialchars($enrollment['ms_remarks']); ?>" readonly>
            </label>
          </div>
        </fieldset>

        <!-- Agreement -->
        <fieldset>
          <legend>Additional Information</legend>
          <label>
            <input type="checkbox" <?php echo $enrollment['advance_course'] ? 'checked' : ''; ?> disabled> 
            Willing to take Cadet Officer Qualifying/Advance Course
          </label>
        </fieldset>

        <!-- Photo -->
        <fieldset>
          <legend>2x2 Photo</legend>
          <?php 
          $photo_exists = false;
          $photo_url = '';
            
          if (!empty($enrollment['photo_path'])) {
              // Check if it's a relative path or absolute path
              if (file_exists($enrollment['photo_path'])) {
                  $photo_exists = true;
                  $photo_url = $enrollment['photo_path'];
              } elseif (file_exists('./' . $enrollment['photo_path'])) {
                  $photo_exists = true;
                  $photo_url = $enrollment['photo_path'];
              } elseif (file_exists('uploads/photos/' . basename($enrollment['photo_path']))) {
                  $photo_exists = true;
                  $photo_url = 'uploads/photos/' . basename($enrollment['photo_path']);
              }
          }
          
          if ($photo_exists): ?>
            <div class="photo-container" style="text-align: center; margin: 20px 0;">
              <img src="<?php echo htmlspecialchars($photo_url); ?>" 
                  alt="Cadet Photo" 
                  style="max-width: 200px; max-height: 200px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                  onclick="window.open(this.src, '_blank')"
                  onerror="this.style.display='none'; document.getElementById('photo-error').style.display='block';">
              <br><small style="color: #666; font-style: italic;">Click to view full size</small>
              <div id="photo-error" style="display: none; color: #dc3545; margin-top: 10px;">
                <p>❌ Photo file not found or corrupted</p>
                <small>Path: <?php echo htmlspecialchars($enrollment['photo_path']); ?></small>
              </div>
            </div>
          <?php else: ?>
            <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; margin: 20px 0;">
              <p style="color: #6c757d; font-size: 18px; margin: 0;">📷 No photo uploaded</p>
              <?php if (!empty($enrollment['photo_path'])): ?>
                <small style="color: #dc3545;">Path stored: <?php echo htmlspecialchars($enrollment['photo_path']); ?></small>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </fieldset>

        <!-- Admin Remarks -->
        <fieldset>
          <legend>Admin Remarks</legend>
          <textarea name="admin_remarks" rows="4" style="width: 100%;" placeholder="Enter remarks for the student..."><?php echo htmlspecialchars($some_variable ?? '');
 ?></textarea>
        </fieldset>

        <!-- Action Buttons -->
        <div class="actions" style="margin-top: 20px; text-align: center;">
          <?php if (in_array($enrollment['status'], ['submitted', 'resubmitted'])): ?>
            <button type="submit" name="approve" class="btn-approve" onclick="return confirm('Are you sure you want to approve this enrollment?')">
              ✓ Approve
            </button>
            <button type="submit" name="reject" class="btn-reject" onclick="return confirm('Are you sure you want to reject this enrollment?')">
              ✗ Reject
            </button>
          <?php endif; ?>
          
          <button type="submit" name="send_remarks" class="btn-remarks">
            💬 Send Remarks
          </button>
          
          <button type="button" onclick="window.location.href='admin.php'" class="btn-close">
            Close
          </button>
        </div>
      </form>
    </div>

    <style>
      .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
      }
      
      .form-row label {
        flex: 1;
      }
      
      .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
      }
      
      .status-submitted { background-color: #fff3cd; color: #856404; }
      .status-resubmitted { background-color: #d1ecf1; color: #0c5460; }
      .status-approved { background-color: #d4edda; color: #155724; }
      .status-rejected { background-color: #f8d7da; color: #721c24; }
      
      .btn-approve { background-color: #28a745; color: white; }
      .btn-reject { background-color: #dc3545; color: white; }
      .btn-remarks { background-color: #17a2b8; color: white; }
      .btn-close { background-color: #6c757d; color: white; }
      .btn-back { background-color: #007bff; color: white; }
      
      .actions button {
        margin: 0 5px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
      }
      
      .actions button:hover {
        opacity: 0.9;
      }
      
      fieldset {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
      }
      
      legend {
        font-weight: bold;
        padding: 0 10px;
      }
    </style>
  </body>
  </html>
