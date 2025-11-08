<?php
require_once '../config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('admin');

$database = new Database();
$db = $database->getConnection();

// Handle logout
if (isset($_POST['logout'])) {
    $sessionManager->logout();
    header('Location: login.php');
    exit();
}

// Get statistics
$stats = [];

// Total enrollments
$query = "SELECT COUNT(*) as total FROM enrollments";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_enrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Enrollments by status
$query = "SELECT status, COUNT(*) as count FROM enrollments GROUP BY status";
$stmt = $db->prepare($query);
$stmt->execute();
$status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($status_counts as $status) {
    $stats['status_' . $status['status']] = $status['count'];
}

// Recent enrollments (last 30 days)
$query = "SELECT COUNT(*) as count FROM enrollments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['recent_enrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Enrollments by course
$query = "SELECT course, COUNT(*) as count FROM enrollments GROUP BY course ORDER BY count DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$course_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly enrollment trends (last 12 months)
$query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as count 
    FROM enrollments 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$monthly_trends = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Age distribution
$query = "SELECT 
    CASE 
        WHEN age < 18 THEN 'Under 18'
        WHEN age BETWEEN 18 AND 20 THEN '18-20'
        WHEN age BETWEEN 21 AND 23 THEN '21-23'
        WHEN age > 23 THEN 'Over 23'
        ELSE 'Unknown'
    END as age_group,
    COUNT(*) as count
    FROM enrollments 
    WHERE age IS NOT NULL
    GROUP BY age_group
    ORDER BY count DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$age_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - Admin Portal</title>
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
    
    .nav-link:hover, .nav-link.active {
      background-color: rgba(255,255,255,0.2);
    }
    
    .btn-logout {
      background: rgba(255,255,255,0.2);
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
    
    .page-header {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .page-title {
      font-size: 2rem;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .page-subtitle {
      color: #666;
      font-size: 1.1rem;
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
      padding: 1.5rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      text-align: center;
      transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: #388e3c;
      margin-bottom: 0.5rem;
    }
    
    .stat-label {
      color: #666;
      font-size: 1rem;
    }
    
    .reports-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 2rem;
    }
    
    .report-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .report-title {
      font-size: 1.3rem;
      color: #333;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid #388e3c;
    }
    
    .report-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .report-table th,
    .report-table td {
      padding: 0.75rem;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    
    .report-table th {
      background: #f8f9fa;
      font-weight: 600;
      color: #333;
    }
    
    .report-table tr:hover {
      background: #f8f9fa;
    }
    
    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }
    
    .status-submitted {
      background: #fff3cd;
      color: #856404;
    }
    
    .status-approved {
      background: #d4edda;
      color: #155724;
    }
    
    .status-rejected {
      background: #f8d7da;
      color: #721c24;
    }
    
    .export-buttons {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    
    .btn-export {
      background: linear-gradient(135deg, #1b5e20 0%, #388e3c 100%);
      color: white;
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-export:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(27, 94, 32, 0.3);
    }
    
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
      
      .reports-grid {
        grid-template-columns: 1fr;
      }
      
      .export-buttons {
        flex-direction: column;
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
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="enrollments.php" class="nav-link">Enrollments</a>
      <a href="reports.php" class="nav-link active">Reports</a>
      <form method="POST" style="display: inline;">
        <button type="submit" name="logout" class="btn-logout">Logout</button>
      </form>
    </div>
  </nav>

  <div class="container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">📊 System Reports</h1>
      <p class="page-subtitle">Comprehensive analytics and enrollment statistics</p>
    </div>

    <!-- Export Buttons -->
    <div class="export-buttons">
      <button class="btn-export" onclick="window.print()">🖨️ Print Report</button>
      <button class="btn-export" onclick="exportToCSV()">📄 Export CSV</button>
      <button class="btn-export" onclick="exportToPDF()">📋 Export PDF</button>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number"><?php echo $stats['total_enrollments'] ?? 0; ?></div>
        <div class="stat-label">Total Enrollments</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $stats['status_submitted'] ?? 0; ?></div>
        <div class="stat-label">Pending Review</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $stats['status_approved'] ?? 0; ?></div>
        <div class="stat-label">Approved</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $stats['recent_enrollments'] ?? 0; ?></div>
        <div class="stat-label">Last 30 Days</div>
      </div>
    </div>

    <!-- Detailed Reports -->
    <div class="reports-grid">
      <!-- Course Distribution -->
      <div class="report-card">
        <h3 class="report-title">Enrollments by Course</h3>
        <table class="report-table">
          <thead>
            <tr>
              <th>Course</th>
              <th>Count</th>
              <th>Percentage</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($course_stats as $course): ?>
              <tr>
                <td><?php echo htmlspecialchars($course['course']); ?></td>
                <td><?php echo $course['count']; ?></td>
                <td><?php echo $stats['total_enrollments'] > 0 ? round(($course['count'] / $stats['total_enrollments']) * 100, 1) : 0; ?>%</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Monthly Trends -->
      <div class="report-card">
        <h3 class="report-title">Monthly Enrollment Trends</h3>
        <table class="report-table">
          <thead>
            <tr>
              <th>Month</th>
              <th>Enrollments</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($monthly_trends as $trend): ?>
              <tr>
                <td><?php echo date('F Y', strtotime($trend['month'] . '-01')); ?></td>
                <td><?php echo $trend['count']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Age Distribution -->
      <div class="report-card">
        <h3 class="report-title">Age Distribution</h3>
        <table class="report-table">
          <thead>
            <tr>
              <th>Age Group</th>
              <th>Count</th>
              <th>Percentage</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($age_distribution as $age): ?>
              <tr>
                <td><?php echo htmlspecialchars($age['age_group']); ?></td>
                <td><?php echo $age['count']; ?></td>
                <td><?php echo $stats['total_enrollments'] > 0 ? round(($age['count'] / $stats['total_enrollments']) * 100, 1) : 0; ?>%</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Status Summary -->
      <div class="report-card">
        <h3 class="report-title">Enrollment Status Summary</h3>
        <table class="report-table">
          <thead>
            <tr>
              <th>Status</th>
              <th>Count</th>
              <th>Percentage</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="status-badge status-submitted">Submitted</span></td>
              <td><?php echo $stats['status_submitted'] ?? 0; ?></td>
              <td><?php echo $stats['total_enrollments'] > 0 ? round((($stats['status_submitted'] ?? 0) / $stats['total_enrollments']) * 100, 1) : 0; ?>%</td>
            </tr>
            <tr>
              <td><span class="status-badge status-approved">Approved</span></td>
              <td><?php echo $stats['status_approved'] ?? 0; ?></td>
              <td><?php echo $stats['total_enrollments'] > 0 ? round((($stats['status_approved'] ?? 0) / $stats['total_enrollments']) * 100, 1) : 0; ?>%</td>
            </tr>
            <tr>
              <td><span class="status-badge status-rejected">Rejected</span></td>
              <td><?php echo $stats['status_rejected'] ?? 0; ?></td>
              <td><?php echo $stats['total_enrollments'] > 0 ? round((($stats['status_rejected'] ?? 0) / $stats['total_enrollments']) * 100, 1) : 0; ?>%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function exportToCSV() {
      // Simple CSV export functionality
      let csvContent = "data:text/csv;charset=utf-8,";
      csvContent += "ROTC Enrollment System Report\n\n";
      csvContent += "Total Enrollments,<?php echo $stats['total_enrollments'] ?? 0; ?>\n";
      csvContent += "Pending Review,<?php echo $stats['status_submitted'] ?? 0; ?>\n";
      csvContent += "Approved,<?php echo $stats['status_approved'] ?? 0; ?>\n";
      csvContent += "Recent (30 days),<?php echo $stats['recent_enrollments'] ?? 0; ?>\n\n";
      
      csvContent += "Course Distribution\n";
      csvContent += "Course,Count,Percentage\n";
      <?php foreach ($course_stats as $course): ?>
        csvContent += "<?php echo htmlspecialchars($course['course']); ?>,<?php echo $course['count']; ?>,<?php echo $stats['total_enrollments'] > 0 ? round(($course['count'] / $stats['total_enrollments']) * 100, 1) : 0; ?>%\n";
      <?php endforeach; ?>

      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "rotc_enrollment_report.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }

    function exportToPDF() {
      alert('PDF export functionality would require additional libraries like jsPDF or server-side PDF generation.');
    }
  </script>
</body>
</html>
