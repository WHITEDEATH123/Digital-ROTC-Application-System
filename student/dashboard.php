<?php
require_once '../config/database.php';

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

// Get enrollment statistics for student
$stats_query = "SELECT 
    COUNT(*) as total_submissions,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
    FROM enrollments WHERE student_id = ?";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute([$student_id]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

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
  <title>Student Dashboard - Digital ROTC</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f8f9fa;
      min-height: 100vh;
    }
    
    .navbar {
      background: linear-gradient(135deg, #2d5016 0%, #4a7c59 100%);
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .navbar-brand {
      font-size: 1.5rem;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .navbar-logo {
      height: 50px;
      width: auto;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      background: white;
      padding: 4px;
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
    
    .btn-logout {
      background-color: rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.3);
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .btn-logout:hover {
      background-color: rgba(255,255,255,0.3);
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .welcome-section {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .welcome-title {
      font-size: 2rem;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .student-info {
      color: #666;
      font-size: 1.1rem;
    }
    
    .dashboard-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 2rem;
      margin-bottom: 2rem;
    }
    
    .status-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .status-header {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 1.5rem;
    }
    
    .status-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: white;
    }
    
    .status-not-enrolled .status-icon { background: #6c757d; }
    .status-submitted .status-icon { background: #ffc107; }
    .status-resubmitted .status-icon { background: #17a2b8; }
    .status-approved .status-icon { background: #28a745; }
    .status-rejected .status-icon { background: #dc3545; }
    
    .status-title {
      font-size: 1.5rem;
      color: #333;
      margin: 0;
    }
    
    .status-desc {
      color: #666;
      margin-top: 1rem;
      line-height: 1.6;
    }
    
    .stats-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .stats-title {
      font-size: 1.2rem;
      color: #333;
      margin-bottom: 1.5rem;
      text-align: center;
    }
    
    .stat-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
      border-bottom: 1px solid #eee;
    }
    
    .stat-item:last-child {
      border-bottom: none;
    }
    
    .stat-label {
      color: #666;
    }
    
    .stat-value {
      font-weight: bold;
      font-size: 1.2rem;
      color: #333;
    }
    
    .remarks-card {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
    }
    
    .remarks-title {
      color: #856404;
      font-size: 1.2rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .remarks-text {
      color: #856404;
      line-height: 1.6;
    }
    
    .actions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
    }
    
    .action-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
    }
    
    .action-card:hover {
      transform: translateY(-5px);
    }
    
    .action-icon {
      width: 60px;
      height: 60px;
      margin: 0 auto 1rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
    }
    
    .action-enroll .action-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .action-review .action-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .action-print .action-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .action-profile .action-icon { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    
    .action-title {
      font-size: 1.2rem;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .action-desc {
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }
    
    .btn {
      display: inline-block;
      padding: 10px 25px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    
    .btn-success {
      background: #28a745;
      color: white;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }
    
    @media (max-width: 768px) {
      .dashboard-grid {
        grid-template-columns: 1fr;
      }
      
      .navbar {
        padding: 1rem;
      }
      
      .navbar-nav {
        gap: 10px;
      }
      
      .container {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation -->
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
      <form method="POST" style="display: inline;">
        <button type="submit" name="logout" class="btn-logout">Logout</button>
      </form>
    </div>
  </nav>

  <div class="container">
    <!-- Welcome Section -->
    <div class="welcome-section">
      <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($current_user['name'] ?: 'Student'); ?>!</h1>
      <p class="student-info">Student ID: <?php echo htmlspecialchars($student_id); ?> | ROTC Enrollment System</p>
    </div>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
      <!-- Status Card -->
      <div class="status-card status-<?php echo $status_class; ?>">
        <div class="status-header">
          <div class="status-icon">
            <?php
            $icons = [
                'not-enrolled' => '📝',
                'submitted' => '⏳',
                'resubmitted' => '🔄',
                'approved' => '✅',
                'rejected' => '❌'
            ];
            echo $icons[$status_class] ?? '📝';
            ?>
          </div>
          <h2 class="status-title"><?php echo $status_title; ?></h2>
        </div>
        <p class="status-desc"><?php echo $status_desc; ?></p>
        
        <?php if ($enrollment): ?>
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #eee;">
          <small style="color: #666;">
            <?php if ($enrollment['submitted_at']): ?>
              Submitted: <?php echo date('M j, Y g:i A', strtotime($enrollment['submitted_at'])); ?>
            <?php endif; ?>
            <?php if ($enrollment['resubmitted_at']): ?>
              <br>Resubmitted: <?php echo date('M j, Y g:i A', strtotime($enrollment['resubmitted_at'])); ?>
            <?php endif; ?>
            <?php if ($enrollment['reviewed_at']): ?>
              <br>Reviewed: <?php echo date('M j, Y g:i A', strtotime($enrollment['reviewed_at'])); ?>
            <?php endif; ?>
          </small>
        </div>
        <?php endif; ?>
      </div>

      <!-- Stats Card -->
      <div class="stats-card">
        <h3 class="stats-title">Your Statistics</h3>
        <div class="stat-item">
          <span class="stat-label">Total Submissions</span>
          <span class="stat-value"><?php echo $stats['total_submissions']; ?></span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Approved</span>
          <span class="stat-value" style="color: #28a745;"><?php echo $stats['approved_count']; ?></span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Rejected</span>
          <span class="stat-value" style="color: #dc3545;"><?php echo $stats['rejected_count']; ?></span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Account Created</span>
          <span class="stat-value"><?php echo date('M Y', strtotime($current_user['created_at'])); ?></span>
        </div>
      </div>
    </div>

    <!-- Admin Remarks -->
    <?php if ($show_remarks): ?>
    <div class="remarks-card">
      <h3 class="remarks-title">
        <span>💬</span>
        Admin Remarks
      </h3>
      <p class="remarks-text"><?php echo nl2br(htmlspecialchars($remarks_text)); ?></p>
    </div>
    <?php endif; ?>

    <!-- Action Cards -->
    <div class="actions-grid">
      <!-- Enroll Action -->
      <div class="action-card action-enroll">
        <div class="action-icon">📝</div>
        <h3 class="action-title">Enrollment Form</h3>
        <p class="action-desc">
          <?php if (!$enrollment): ?>
            Start your ROTC enrollment process
          <?php elseif ($enrollment['status'] == 'rejected'): ?>
            Update and resubmit your form
          <?php else: ?>
            View or update your enrollment
          <?php endif; ?>
        </p>
        <a href="enroll.php" class="btn btn-primary">
          <?php echo !$enrollment ? 'Start Enrollment' : 'View Form'; ?>
        </a>
      </div>

      <!-- Review Action -->
      <div class="action-card action-review">
        <div class="action-icon">👁️</div>
        <h3 class="action-title">Review Submission</h3>
        <p class="action-desc">Preview your enrollment form and check all details</p>
        <a href="review.php" class="btn btn-secondary">View Submission</a>
      </div>

      <!-- Print Action -->
      <div class="action-card action-print">
        <div class="action-icon">🖨️</div>
        <h3 class="action-title">Print Certificate</h3>
        <p class="action-desc">Download your official enrollment certificate</p>
        <?php if ($enrollment && $enrollment['status'] == 'approved'): ?>
          <a href="certificate.php" class="btn btn-success">Print Certificate</a>
        <?php else: ?>
          <button class="btn btn-secondary" disabled>Approval Required</button>
        <?php endif; ?>
      </div>

      <!-- Profile Action -->
      <div class="action-card action-profile">
        <div class="action-icon">👤</div>
        <h3 class="action-title">My Profile</h3>
        <p class="action-desc">Manage your account settings and personal information</p>
        <a href="profile.php" class="btn btn-primary">Manage Profile</a>
      </div>
    </div>
  </div>
</body>
</html>
