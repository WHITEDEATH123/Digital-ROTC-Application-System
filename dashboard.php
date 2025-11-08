<?php
require_once 'config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('student');

$database = new Database();
$db = $database->getConnection();

$current_user = $sessionManager->getCurrentUser();
$student_id = $_SESSION['user_id'];

// Get enrollment information
$query = "SELECT * FROM enrollments WHERE student_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([$student_id]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

// Determine status and messages
$status_title = "Not Yet Enrolled";
$status_desc = "You have not started enrollment yet.";
$status_class = "not-enrolled";
$show_remarks = false;
$remarks_text = "";

if ($enrollment) {
    switch ($enrollment['status']) {
        case 'submitted':
            $status_title = "Enrollment Submitted";
            $status_desc = "Your enrollment form has been submitted and is pending review.";
            $status_class = "submitted";
            break;
        case 'resubmitted':
            $status_title = "Form Resubmitted";
            $status_desc = "Your updated form has been resubmitted for review.";
            $status_class = "resubmitted";
            break;
        case 'approved':
            $status_title = "Enrollment Approved";
            $status_desc = "Congratulations! Your enrollment has been approved.";
            $status_class = "approved";
            break;
        case 'rejected':
            $status_title = "Enrollment Rejected";
            $status_desc = "Your enrollment needs revision. Please check the remarks below.";
            $status_class = "rejected";
            break;
    }
    
    if (!empty($enrollment['admin_remarks'])) {
        $show_remarks = true;
        $remarks_text = $enrollment['admin_remarks'];
    }
}

// Handle logout
if (isset($_POST['logout'])) {
    $sessionManager->logout();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Digital ROTC - Dashboard</title>
  <link rel="stylesheet" href="assets/css/dashboard.css" />
</head>
<body>
  <div class="topbar">
    <div class="brand">Digital ROTC</div>
    <div class="actions">
      <button onclick="window.location.href='enroll.php'">Enroll Now</button>
      <form method="POST" style="display: inline;">
        <button type="submit" name="logout" class="danger">Log Out</button>
      </form>
    </div>
  </div>

  <main class="main">
    <div class="card">
      <!-- Logo -->
      <div class="logo-container">
        <img src="assets/img/jhcsc-logo.png" alt="JHCSC Logo" class="logo">
      </div>

      <h2>Welcome, <span id="cadetName"><?php echo htmlspecialchars($current_user['name'] ?: 'Cadet'); ?></span></h2>

      <div class="status-row">
        <div class="status-box <?php echo $status_class; ?>" id="statusBox">
          <h3 id="statusTitle"><?php echo $status_title; ?></h3>
          <p id="statusDesc"><?php echo $status_desc; ?></p>
        </div>
        
        <?php if ($show_remarks): ?>
        <div class="remarks-box" id="remarksBox">
          <h4>Admin Remarks</h4>
          <p id="remarkText"><?php echo htmlspecialchars($remarks_text); ?></p>
        </div>
        <?php endif; ?>
      </div>

      <div class="buttons">
        <button onclick="window.location.href='review.php'" class="secondary">View Submission / Review</button>
        <?php if ($enrollment && $enrollment['status'] == 'approved'): ?>
        <button onclick="window.location.href='cadetview.php'" class="primary">Print Enrollment Form</button>
        <?php else: ?>
        <button onclick="alert('Form must be approved before printing')" class="primary" disabled>Print Enrollment Form</button>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <style>
    .status-box.not-enrolled { background-color: #f8f9fa; border-left: 4px solid #6c757d; }
    .status-box.submitted { background-color: #fff3cd; border-left: 4px solid #ffc107; }
    .status-box.resubmitted { background-color: #d1ecf1; border-left: 4px solid #17a2b8; }
    .status-box.approved { background-color: #d4edda; border-left: 4px solid #28a745; }
    .status-box.rejected { background-color: #f8d7da; border-left: 4px solid #dc3545; }
    
    .remarks-box {
      background-color: #e2e3e5;
      border: 1px solid #d6d8db;
      border-radius: 5px;
      padding: 15px;
      margin-top: 15px;
    }
    
    .remarks-box h4 {
      margin: 0 0 10px 0;
      color: #495057;
    }
    
    button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  </style>
</body>
</html>
