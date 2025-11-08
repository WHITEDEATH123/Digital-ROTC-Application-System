<?php
require_once 'config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('student');

$database = new Database();
$db = $database->getConnection();

$student_id = $_SESSION['user_id'];

// Get enrollment information
$query = "SELECT * FROM enrollments WHERE student_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([$student_id]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])) {
    if ($enrollment && $enrollment['status'] == 'submitted') {
        // Update status to confirmed (this could trigger admin notification)
        $update_query = "UPDATE enrollments SET updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($update_query);
        $stmt->execute([$enrollment['id']]);
        
        header('Location: dashboard.php?confirmed=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Digital ROTC - Review</title>
  <link rel="stylesheet" href="assets/css/review.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="container">
    <header>
      <h1>Review Your Submission</h1>
      <button onclick="window.location.href='dashboard.php'" class="btn-back no-print">← Back to Dashboard</button>
    </header>

    <?php if (!$enrollment): ?>
      <div class="review-card">
        <div class="no-submission">
          <h2>No Submission Found</h2>
          <p>You haven't submitted an enrollment form yet.</p>
          <button onclick="window.location.href='enroll.php'" class="btn-primary">Start Enrollment</button>
        </div>
      </div>
    <?php else: ?>
      <div class="review-card" id="reviewCard">
        <div class="status-header">
          <h2>Enrollment Status: 
            <span class="status-badge status-<?php echo $enrollment['status']; ?>">
              <?php echo ucfirst($enrollment['status']); ?>
            </span>
          </h2>
          <p class="submission-date">
            <?php if ($enrollment['status'] == 'resubmitted'): ?>
              Resubmitted: <?php echo date('F j, Y g:i A', strtotime($enrollment['resubmitted_at'])); ?>
            <?php else: ?>
              Submitted: <?php echo date('F j, Y g:i A', strtotime($enrollment['submitted_at'] ?: $enrollment['created_at'])); ?>
            <?php endif; ?>
          </p>
        </div>

        <!-- Personal Information -->
        <div class="info-section">
          <h3>Personal Information</h3>
          <div class="info-grid">
            <div class="info-item">
              <label>Full Name:</label>
              <span><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['middle_name'] . ' ' . $enrollment['last_name']); ?></span>
            </div>
            <div class="info-item">
              <label>Age:</label>
              <span><?php echo $enrollment['age']; ?></span>
            </div>
            <div class="info-item">
              <label>Date of Birth:</label>
              <span><?php echo date('F j, Y', strtotime($enrollment['date_of_birth'])); ?></span>
            </div>
            <div class="info-item">
              <label>Place of Birth:</label>
              <span><?php echo htmlspecialchars($enrollment['place_of_birth']); ?></span>
            </div>
            <div class="info-item">
              <label>Religion:</label>
              <span><?php echo htmlspecialchars($enrollment['religion']); ?></span>
            </div>
            <div class="info-item">
              <label>Blood Type:</label>
              <span><?php echo htmlspecialchars($enrollment['blood_type']); ?></span>
            </div>
            <div class="info-item">
              <label>Height:</label>
              <span><?php echo $enrollment['height']; ?> cm</span>
            </div>
            <div class="info-item">
              <label>Weight:</label>
              <span><?php echo $enrollment['weight']; ?> kg</span>
            </div>
            <div class="info-item">
              <label>Complexion:</label>
              <span><?php echo htmlspecialchars($enrollment['complexion']); ?></span>
            </div>
          </div>
        </div>

        <!-- Academic Information -->
        <div class="info-section">
          <h3>Academic Information</h3>
          <div class="info-grid">
            <div class="info-item">
              <label>Block:</label>
              <span><?php echo htmlspecialchars($enrollment['block']); ?></span>
            </div>
            <div class="info-item">
              <label>Course:</label>
              <span><?php echo htmlspecialchars($enrollment['course']); ?></span>
            </div>
            <div class="info-item">
              <label>Army NSTP:</label>
              <span><?php echo htmlspecialchars($enrollment['army_nstp']); ?></span>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="info-section">
          <h3>Contact Information</h3>
          <div class="info-grid">
            <div class="info-item full-width">
              <label>Address:</label>
              <span><?php echo htmlspecialchars($enrollment['address']); ?></span>
            </div>
            <div class="info-item">
              <label>Phone:</label>
              <span><?php echo htmlspecialchars($enrollment['phone']); ?></span>
            </div>
            <div class="info-item">
              <label>Email:</label>
              <span><?php echo htmlspecialchars($enrollment['email']); ?></span>
            </div>
          </div>
        </div>

        <!-- Parent/Guardian Information -->
        <div class="info-section">
          <h3>Parent/Guardian Information</h3>
          <div class="info-grid">
            <div class="info-item">
              <label>Father's Name:</label>
              <span><?php echo htmlspecialchars($enrollment['father_name']); ?></span>
            </div>
            <div class="info-item">
              <label>Father's Occupation:</label>
              <span><?php echo htmlspecialchars($enrollment['father_occupation']); ?></span>
            </div>
            <div class="info-item">
              <label>Mother's Name:</label>
              <span><?php echo htmlspecialchars($enrollment['mother_name']); ?></span>
            </div>
            <div class="info-item">
              <label>Mother's Occupation:</label>
              <span><?php echo htmlspecialchars($enrollment['mother_occupation']); ?></span>
            </div>
            <div class="info-item">
              <label>Emergency Contact:</label>
              <span><?php echo htmlspecialchars($enrollment['emergency_contact_person']); ?></span>
            </div>
            <div class="info-item">
              <label>Relationship:</label>
              <span><?php echo htmlspecialchars($enrollment['relationship']); ?></span>
            </div>
            <div class="info-item">
              <label>Emergency Phone:</label>
              <span><?php echo htmlspecialchars($enrollment['emergency_contact_number']); ?></span>
            </div>
          </div>
        </div>

        <!-- Military Science Information -->
        <?php if (!empty($enrollment['ms']) || !empty($enrollment['semester']) || !empty($enrollment['school_year'])): ?>
        <div class="info-section">
          <h3>Military Science Completed</h3>
          <div class="info-grid">
            <div class="info-item">
              <label>MS:</label>
              <span><?php echo htmlspecialchars($enrollment['ms']); ?></span>
            </div>
            <div class="info-item">
              <label>Semester:</label>
              <span><?php echo htmlspecialchars($enrollment['semester']); ?></span>
            </div>
            <div class="info-item">
              <label>School Year:</label>
              <span><?php echo htmlspecialchars($enrollment['school_year']); ?></span>
            </div>
            <div class="info-item">
              <label>Grade:</label>
              <span><?php echo htmlspecialchars($enrollment['grade']); ?></span>
            </div>
            <div class="info-item full-width">
              <label>Remarks:</label>
              <span><?php echo htmlspecialchars($enrollment['ms_remarks']); ?></span>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Additional Information -->
        <div class="info-section">
          <h3>Additional Information</h3>
          <div class="info-item">
            <label>Advance Course:</label>
            <span><?php echo $enrollment['advance_course'] ? 'Yes, willing to take Cadet Officer Qualifying/Advance Course' : 'No'; ?></span>
          </div>
        </div>

        <!-- Photo -->
        <div class="info-section">
          <h3>2x2 Photo</h3>
          <div class="photo-container">
            <?php if (!empty($enrollment['photo_path']) && file_exists($enrollment['photo_path'])): ?>
              <img src="<?php echo htmlspecialchars($enrollment['photo_path']); ?>" 
                   alt="Cadet Photo" 
                   style="max-width: 150px; max-height: 150px; border: 2px solid #ddd; border-radius: 5px;">
            <?php else: ?>
              <div class="no-photo">No photo uploaded</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Admin Remarks -->
        <?php if (!empty($enrollment['admin_remarks'])): ?>
        <div class="info-section">
          <h3>Admin Remarks</h3>
          <div class="remarks-box">
            <?php echo nl2br(htmlspecialchars($enrollment['admin_remarks'])); ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <div class="actions no-print">
        <?php if (in_array($enrollment['status'], ['submitted', 'resubmitted', 'rejected'])): ?>
          <button onclick="window.location.href='enroll.php'" class="btn-edit">✏️ Edit Form</button>
        <?php endif; ?>
        
        <?php if ($enrollment['status'] == 'submitted'): ?>
          <form method="POST" style="display: inline;">
            <button type="submit" name="confirm" class="btn-confirm" onclick="return confirm('Are you sure you want to confirm this enrollment? You cannot edit it after confirmation.')">
              ✅ Confirm Enrollment
            </button>
          </form>
        <?php endif; ?>
        
        <?php if ($enrollment['status'] == 'approved'): ?>
          <button onclick="window.location.href='cadetview.php'" class="btn-print">🖨️ Print Form</button>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <style>
    .btn-back {
      background-color: #6c757d;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      float: right;
    }
    
    .status-header {
      text-align: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #eee;
    }
    
    .status-badge {
      padding: 6px 12px;
      border-radius: 15px;
      font-size: 14px;
      font-weight: bold;
      text-transform: uppercase;
    }
    
    .status-submitted { background-color: #fff3cd; color: #856404; }
    .status-resubmitted { background-color: #d1ecf1; color: #0c5460; }
    .status-approved { background-color: #d4edda; color: #155724; }
    .status-rejected { background-color: #f8d7da; color: #721c24; }
    
    .submission-date {
      color: #666;
      font-style: italic;
      margin-top: 10px;
    }
    
    .info-section {
      margin-bottom: 25px;
      padding: 20px;
      background-color: #f8f9fa;
      border-radius: 8px;
    }
    
    .info-section h3 {
      margin: 0 0 15px 0;
      color: #333;
      border-bottom: 1px solid #dee2e6;
      padding-bottom: 8px;
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
    }
    
    .info-item {
      display: flex;
      flex-direction: column;
    }
    
    .info-item.full-width {
      grid-column: 1 / -1;
    }
    
    .info-item label {
      font-weight: bold;
      color: #555;
      margin-bottom: 5px;
    }
    
    .info-item span {
      color: #333;
      padding: 5px 0;
    }
    
    .photo-container {
      text-align: center;
    }
    
    .no-photo {
      display: inline-block;
      width: 150px;
      height: 150px;
      border: 2px dashed #ddd;
      line-height: 150px;
      color: #999;
      border-radius: 5px;
    }
    
    .remarks-box {
      background-color: #fff;
      border: 1px solid #dee2e6;
      border-radius: 5px;
      padding: 15px;
      color: #333;
    }
    
    .actions {
      text-align: center;
      margin-top: 30px;
    }
    
    .actions button {
      margin: 0 10px;
      padding: 12px 24px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      font-size: 14px;
    }
    
    .btn-edit {
      background-color: #ffc107;
      color: #212529;
    }
    
    .btn-confirm {
      background-color: #28a745;
      color: white;
    }
    
    .btn-print {
      background-color: #007bff;
      color: white;
    }
    
    .no-submission {
      text-align: center;
      padding: 50px;
    }
    
    .btn-primary {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }
    
    @media print {
      .no-print { display: none !important; }
    }
  </style>
</body>
</html>
