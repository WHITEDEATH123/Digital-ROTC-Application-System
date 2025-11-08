<?php
require_once 'config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('admin');

$database = new Database();
$db = $database->getConnection();

// Handle logout
if (isset($_POST['logout'])) {
    $sessionManager->logout();
    header('Location: admin/login.php');
    exit();
}

// Get pending enrollments
$pending_query = "SELECT e.*, u.name as student_name 
                  FROM enrollments e 
                  JOIN users u ON e.student_id = u.id 
                  WHERE e.status IN ('submitted', 'resubmitted') 
                  ORDER BY e.updated_at DESC";
$pending_stmt = $db->prepare($pending_query);
$pending_stmt->execute();
$pending_enrollments = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get approved enrollments
$approved_query = "SELECT e.*, u.name as student_name 
                   FROM enrollments e 
                   JOIN users u ON e.student_id = u.id 
                   WHERE e.status = 'approved' 
                   ORDER BY e.updated_at DESC";
$approved_stmt = $db->prepare($approved_query);
$approved_stmt->execute();
$approved_enrollments = $approved_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ROTC Admin Panel</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
  <div class="admin-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h1>ROTC Admin Panel</h1>
      <form method="POST" style="display: inline;">
        <button type="submit" name="logout" class="logout-btn">Log Out</button>
      </form>
    </div>

    <!-- Pending Enrollments -->
    <div class="table-container">
      <div class="table-header">
        <img src="assets/img/jhcsc-logo.png" alt="JHCSC Logo" class="logo">
        <h2>Pending Enrollments (<?php echo count($pending_enrollments); ?>)</h2>
      </div>
      <table id="pendingTable">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Block</th>
            <th>Course</th>
            <th>Army ROTC</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pending_enrollments)): ?>
            <tr>
              <td colspan="8" style="text-align: center; color: #666; padding: 20px;">
                No pending enrollments
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pending_enrollments as $enrollment): ?>
            <tr>
              <td><?php echo htmlspecialchars($enrollment['student_id']); ?></td>
              <td><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></td>
              <td><?php echo htmlspecialchars($enrollment['block']); ?></td>
              <td><?php echo htmlspecialchars($enrollment['course']); ?></td>
              <td><?php echo htmlspecialchars($enrollment['army_nstp']); ?></td>
              <td>
                <span class="status-badge status-<?php echo $enrollment['status']; ?>">
                  <?php echo ucfirst($enrollment['status']); ?>
                </span>
              </td>
              <td><?php echo date('M j, Y', strtotime($enrollment['submitted_at'] ?: $enrollment['created_at'])); ?></td>
              <td>
                <button onclick="viewEnrollment(<?php echo $enrollment['id']; ?>)" class="btn-view">View</button>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Approved Enrollments -->
    <div class="table-container">
      <div class="table-header">
        <img src="assets/img/jhcsc-logo.png" alt="JHCSC Logo" class="logo">
        <h2>Approved Enrollments (<?php echo count($approved_enrollments); ?>)</h2>
      </div>
      <table id="approvedTable">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Block</th>
            <th>Course</th>
            <th>Army ROTC</th>
            <th>Approved Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($approved_enrollments)): ?>
            <tr>
              <td colspan="7" style="text-align: center; color: #666; padding: 20px;">
                No approved enrollments
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($approved_enrollments as $enrollment): ?>
            <tr>
              <td><?php echo htmlspecialchars($enrollment['student_id']); ?></td>
              <td><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></td>
              <td><?php echo htmlspecialchars($enrollment['block']); ?></td>
              <td><?php echo htmlspecialchars($enrollment['course']); ?></td>
              <td><?php echo htmlspecialchars($enrollment['army_nstp']); ?></td>
              <td><?php echo date('M j, Y', strtotime($enrollment['reviewed_at'] ?: $enrollment['updated_at'])); ?></td>
              <td>
                <button onclick="viewEnrollment(<?php echo $enrollment['id']; ?>)" class="btn-view">View</button>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <style>
    .status-badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
    }
    
    .status-submitted {
      background-color: #fff3cd;
      color: #856404;
    }
    
    .status-resubmitted {
      background-color: #d1ecf1;
      color: #0c5460;
    }
    
    .btn-view {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 3px;
      cursor: pointer;
      font-size: 12px;
    }
    
    .btn-view:hover {
      background-color: #0056b3;
    }
    
    .logout-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
    }
    
    .logout-btn:hover {
      background-color: #c82333;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    th {
      background-color: #f8f9fa;
      font-weight: bold;
    }
    
    tr:hover {
      background-color: #f5f5f5;
    }
  </style>

  <script>
    function viewEnrollment(enrollmentId) {
      window.location.href = 'view.php?id=' + enrollmentId;
    }
  </script>
</body>
</html>
