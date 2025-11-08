<?php
require_once '../config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('admin');

$database = new Database();
$db = $database->getConnection();

$current_user = $sessionManager->getCurrentUser();

// Get comprehensive statistics
$stats = [];

// Total enrollments by status
$status_query = "SELECT status, COUNT(*) as count FROM enrollments GROUP BY status";
$status_stmt = $db->prepare($status_query);
$status_stmt->execute();
$status_results = $status_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($status_results as $row) {
    $stats[$row['status']] = $row['count'];
}

// Total students
$total_students_query = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
$total_students_stmt = $db->prepare($total_students_query);
$total_students_stmt->execute();
$stats['total_students'] = $total_students_stmt->fetch()['count'];

// Recent activity (last 7 days)
$recent_query = "SELECT COUNT(*) as count FROM enrollments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_stmt = $db->prepare($recent_query);
$recent_stmt->execute();
$stats['recent_enrollments'] = $recent_stmt->fetch()['count'];

// Pending reviews
$pending_query = "SELECT COUNT(*) as count FROM enrollments WHERE status IN ('submitted', 'resubmitted')";
$pending_stmt = $db->prepare($pending_query);
$pending_stmt->execute();
$stats['pending_reviews'] = $pending_stmt->fetch()['count'];

// Get recent enrollments for quick access
$recent_enrollments_query = "SELECT e.*, u.name as student_name 
                            FROM enrollments e 
                            JOIN users u ON e.student_id = u.id 
                            WHERE e.status IN ('submitted', 'resubmitted')
                            ORDER BY e.updated_at DESC 
                            LIMIT 5";
$recent_enrollments_stmt = $db->prepare($recent_enrollments_query);
$recent_enrollments_stmt->execute();
$recent_enrollments = $recent_enrollments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get monthly enrollment trends
$trends_query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as count
    FROM enrollments 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC";
$trends_stmt = $db->prepare($trends_query);
$trends_stmt->execute();
$trends = $trends_stmt->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Admin Dashboard - Digital ROTC</title>
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
      background: linear-gradient(135deg, #1b5e20 0%, #388e3c 100%);
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
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .welcome-section {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .welcome-content h1 {
      font-size: 2rem;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .welcome-content p {
      color: #666;
      font-size: 1.1rem;
    }
    
    .quick-stats {
      display: flex;
      gap: 2rem;
      align-items: center;
    }
    
    .quick-stat {
      text-align: center;
    }
    
    .quick-stat-number {
      font-size: 2rem;
      font-weight: bold;
      color: #ff6b6b;
    }
    
    .quick-stat-label {
      font-size: 0.9rem;
      color: #666;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
    }
    
    .stat-card.pending::before { background: #ffc107; }
    .stat-card.approved::before { background: #28a745; }
    .stat-card.rejected::before { background: #dc3545; }
    .stat-card.students::before { background: #007bff; }
    
    .stat-icon {
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
    
    .stat-card.pending .stat-icon { background: #ffc107; }
    .stat-card.approved .stat-icon { background: #28a745; }
    .stat-card.rejected .stat-icon { background: #dc3545; }
    .stat-card.students .stat-icon { background: #007bff; }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .stat-label {
      color: #666;
      font-size: 1rem;
    }
    
    .main-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 2rem;
    }
    
    .recent-enrollments {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .section-title {
      font-size: 1.3rem;
      color: #333;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .enrollment-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      border-bottom: 1px solid #eee;
      transition: background-color 0.3s;
    }
    
    .enrollment-item:hover {
      background-color: #f8f9fa;
    }
    
    .enrollment-item:last-child {
      border-bottom: none;
    }
    
    .enrollment-info h4 {
      color: #333;
      margin-bottom: 0.25rem;
    }
    
    .enrollment-info p {
      color: #666;
      font-size: 0.9rem;
    }
    
    .status-badge {
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: bold;
      text-transform: uppercase;
    }
    
    .status-submitted { background: #fff3cd; color: #856404; }
    .status-resubmitted { background: #d1ecf1; color: #0c5460; }
    
    .trends-section {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .trend-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
      border-bottom: 1px solid #eee;
    }
    
    .trend-item:last-child {
      border-bottom: none;
    }
    
    .trend-month {
      font-weight: 500;
      color: #333;
    }
    
    .trend-count {
      background: #e3f2fd;
      color: #1976d2;
      padding: 4px 12px;
      border-radius: 12px;
      font-weight: bold;
    }
    
    .actions-section {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      margin-top: 2rem;
    }
    
    .action-buttons {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }
    
    .action-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 1rem 1.5rem;
      border: none;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      text-align: center;
      justify-content: center;
    }
    
    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .btn-primary { background: #007bff; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-warning { background: #ffc107; color: #212529; }
    .btn-info { background: #17a2b8; color: white; }
    
    .no-data {
      text-align: center;
      color: #666;
      padding: 2rem;
      font-style: italic;
    }
    
    @media (max-width: 768px) {
      .main-grid {
        grid-template-columns: 1fr;
      }
      
      .welcome-section {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
      }
      
      .quick-stats {
        flex-direction: column;
        gap: 1rem;
      }
      
      .navbar {
        padding: 1rem;
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
        <div style="font-size: 1.2rem;">Admin Portal</div>
        <div style="font-size: 0.8rem; opacity: 0.9;">Digital ROTC System</div>
      </div>
    </div>
    <div class="navbar-nav">
      <a href="dashboard.php" class="nav-link active">Dashboard</a>
      <a href="enrollments.php" class="nav-link">Enrollments</a>
      <a href="students.php" class="nav-link">Students</a>
      <a href="reports.php" class="nav-link">Reports</a>
      <form method="POST" style="display: inline;">
        <button type="submit" name="logout" class="btn-logout">Logout</button>
      </form>
    </div>
  </nav>

  <div class="container">
    <!-- Welcome Section -->
    <div class="welcome-section">
      <div class="welcome-content">
        <h1>Welcome, <?php echo htmlspecialchars($current_user['name'] ?: 'Administrator'); ?>!</h1>
        <p>ROTC Enrollment Management System</p>
      </div>
      <div class="quick-stats">
        <div class="quick-stat">
          <div class="quick-stat-number"><?php echo $stats['pending_reviews'] ?? 0; ?></div>
          <div class="quick-stat-label">Pending Reviews</div>
        </div>
        <div class="quick-stat">
          <div class="quick-stat-number"><?php echo $stats['recent_enrollments'] ?? 0; ?></div>
          <div class="quick-stat-label">This Week</div>
        </div>
        <div class="quick-stat">
          <div class="quick-stat-number"><?php echo $stats['total_students'] ?? 0; ?></div>
          <div class="quick-stat-label">Total Students</div>
        </div>
      </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
      <div class="stat-card pending">
        <div class="stat-icon">⏳</div>
        <div class="stat-number"><?php echo $stats['submitted'] ?? 0; ?></div>
        <div class="stat-label">Pending Enrollments</div>
      </div>
      
      <div class="stat-card approved">
        <div class="stat-icon">✅</div>
        <div class="stat-number"><?php echo $stats['approved'] ?? 0; ?></div>
        <div class="stat-label">Approved Enrollments</div>
      </div>
      
      <div class="stat-card rejected">
        <div class="stat-icon">❌</div>
        <div class="stat-number"><?php echo $stats['rejected'] ?? 0; ?></div>
        <div class="stat-label">Rejected Enrollments</div>
      </div>
      
      <div class="stat-card students">
        <div class="stat-icon">👥</div>
        <div class="stat-number"><?php echo $stats['total_students'] ?? 0; ?></div>
        <div class="stat-label">Registered Students</div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="main-grid">
      <!-- Recent Enrollments -->
      <div class="recent-enrollments">
        <h2 class="section-title">
          <span>📋</span>
          Recent Enrollment Submissions
        </h2>
        
        <?php if (empty($recent_enrollments)): ?>
          <div class="no-data">No pending enrollments at this time.</div>
        <?php else: ?>
          <?php foreach ($recent_enrollments as $enrollment): ?>
            <div class="enrollment-item">
              <div class="enrollment-info">
                <h4><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></h4>
                <p>Student ID: <?php echo htmlspecialchars($enrollment['student_id']); ?> | 
                   <?php echo htmlspecialchars($enrollment['course']); ?> - Block <?php echo htmlspecialchars($enrollment['block']); ?></p>
                <p>Submitted: <?php echo date('M j, Y g:i A', strtotime($enrollment['updated_at'])); ?></p>
              </div>
              <div>
                <span class="status-badge status-<?php echo $enrollment['status']; ?>">
                  <?php echo ucfirst($enrollment['status']); ?>
                </span>
                <br><br>
                <a href="../view.php?id=<?php echo $enrollment['id']; ?>" 
                   style="background: #007bff; color: white; padding: 5px 15px; border-radius: 15px; text-decoration: none; font-size: 0.8rem;">
                  Review
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Enrollment Trends -->
      <div class="trends-section">
        <h2 class="section-title">
          <span>📈</span>
          Enrollment Trends
        </h2>
        
        <?php if (empty($trends)): ?>
          <div class="no-data">No enrollment data available.</div>
        <?php else: ?>
          <?php foreach ($trends as $trend): ?>
            <div class="trend-item">
              <span class="trend-month"><?php echo date('F Y', strtotime($trend['month'] . '-01')); ?></span>
              <span class="trend-count"><?php echo $trend['count']; ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="actions-section">
      <h2 class="section-title">
        <span>⚡</span>
        Quick Actions
      </h2>
      
      <div class="action-buttons">
        <a href="enrollments.php" class="action-btn btn-primary">
          <span>📋</span>
          Manage Enrollments
        </a>
        
        <a href="students.php" class="action-btn btn-success">
          <span>👥</span>
          View All Students
        </a>
        
        <a href="reports.php" class="action-btn btn-warning">
          <span>📊</span>
          Generate Reports
        </a>
        
        <a href="settings.php" class="action-btn btn-info">
          <span>⚙️</span>
          System Settings
        </a>
      </div>
    </div>
  </div>
</body>
</html>
