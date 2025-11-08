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

// Handle search and filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';

// Build query with filters
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.student_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "e.status = ?";
    $params[] = $status_filter;
}

if (!empty($course_filter)) {
    $where_conditions[] = "e.course = ?";
    $params[] = $course_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get students with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$query = "SELECT e.* 
          FROM enrollments e 
          $where_clause
          ORDER BY e.created_at DESC 
          LIMIT $limit OFFSET $offset";

$stmt = $db->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: Check if query is working
if (empty($students)) {
    // Try a simple query without filters
    $debug_query = "SELECT COUNT(*) as total FROM enrollments";
    $debug_stmt = $db->prepare($debug_query);
    $debug_stmt->execute();
    $debug_result = $debug_stmt->fetch(PDO::FETCH_ASSOC);
    $debug_total = $debug_result['total'];
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM enrollments e $where_clause";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_students = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_students / $limit);

// Get unique courses for filter dropdown
$courses_query = "SELECT DISTINCT course FROM enrollments WHERE course IS NOT NULL ORDER BY course";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Students - Admin Portal</title>
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
      max-width: 1400px;
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
    
    .filters-section {
      background: white;
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .filters-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      align-items: end;
    }
    
    .filter-group {
      display: flex;
      flex-direction: column;
    }
    
    .filter-group label {
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #333;
    }
    
    .filter-group input,
    .filter-group select {
      padding: 0.75rem;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }
    
    .filter-group input:focus,
    .filter-group select:focus {
      outline: none;
      border-color: #ff6b6b;
    }
    
    .btn-filter {
      background: linear-gradient(135deg, #1b5e20 0%, #388e3c 100%);
      color: white;
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .btn-filter:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(27, 94, 32, 0.3);
    }
    
    .students-table {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
    }
    
    .table-header {
      background: linear-gradient(135deg, #1b5e20 0%, #388e3c 100%);
      color: white;
      padding: 1rem;
      font-size: 1.2rem;
      font-weight: 600;
    }
    
    .table-responsive {
      overflow-x: auto;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
    }
    
    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    
    th {
      background: #f8f9fa;
      font-weight: 600;
      color: #333;
      position: sticky;
      top: 0;
    }
    
    tr:hover {
      background: #f8f9fa;
    }
    
    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }
    
    .status-not_enrolled {
      background: #e2e3e5;
      color: #383d41;
    }
    
    .status-submitted {
      background: #fff3cd;
      color: #856404;
    }
    
    .status-resubmitted {
      background: #d1ecf1;
      color: #0c5460;
    }
    
    .status-approved {
      background: #d4edda;
      color: #155724;
    }
    
    .status-rejected {
      background: #f8d7da;
      color: #721c24;
    }
    
    .btn-view {
      background: #007bff;
      color: white;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.9rem;
      text-decoration: none;
      display: inline-block;
      transition: background-color 0.3s;
    }
    
    .btn-view:hover {
      background: #0056b3;
    }
    
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 0.5rem;
      margin-top: 2rem;
    }
    
    .pagination a,
    .pagination span {
      padding: 0.5rem 1rem;
      border: 1px solid #ddd;
      border-radius: 5px;
      text-decoration: none;
      color: #333;
      transition: all 0.3s;
    }
    
    .pagination a:hover {
      background: #388e3c;
      color: white;
      border-color: #388e3c;
    }
    
    .pagination .current {
      background: #388e3c;
      color: white;
      border-color: #388e3c;
    }
    
    .no-results {
      text-align: center;
      padding: 3rem;
      color: #666;
      font-size: 1.1rem;
    }
    
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      .filters-grid {
        grid-template-columns: 1fr;
      }
      
      .table-responsive {
        font-size: 0.9rem;
      }
      
      th, td {
        padding: 0.5rem;
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
      <a href="students.php" class="nav-link active">Students</a>
      <a href="reports.php" class="nav-link">Reports</a>
      <form method="POST" style="display: inline;">
        <button type="submit" name="logout" class="btn-logout">Logout</button>
      </form>
    </div>
  </nav>

  <div class="container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">👥 Student Management</h1>
      <p class="page-subtitle">View and manage all enrolled students</p>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
      <form method="GET" action="">
        <div class="filters-grid">
          <div class="filter-group">
            <label for="search">Search Students</label>
            <input type="text" id="search" name="search" placeholder="Name or Student ID" value="<?php echo htmlspecialchars($search); ?>">
          </div>
          
          <div class="filter-group">
            <label for="status">Status</label>
            <select id="status" name="status">
              <option value="">All Statuses</option>
              <option value="not_enrolled" <?php echo $status_filter === 'not_enrolled' ? 'selected' : ''; ?>>Not Enrolled</option>
              <option value="submitted" <?php echo $status_filter === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
              <option value="resubmitted" <?php echo $status_filter === 'resubmitted' ? 'selected' : ''; ?>>Resubmitted</option>
              <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
              <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label for="course">Course</label>
            <select id="course" name="course">
              <option value="">All Courses</option>
              <?php foreach ($courses as $course): ?>
                <option value="<?php echo htmlspecialchars($course); ?>" <?php echo $course_filter === $course ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($course); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="filter-group">
            <button type="submit" class="btn-filter">🔍 Filter</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Students Table -->
    <div class="students-table">
      <div class="table-header">
        📋 Students List (<?php echo $total_students; ?> total)
      </div>
      
      <?php if (empty($students)): ?>
        <div class="no-results">
          <p>No students found matching your criteria.</p>
          <?php if (isset($debug_total)): ?>
            <p><small>Debug: Total enrollments in database: <?php echo $debug_total; ?></small></p>
            <p><small>Applied filters: Search="<?php echo htmlspecialchars($search); ?>", Status="<?php echo htmlspecialchars($status_filter); ?>", Course="<?php echo htmlspecialchars($course_filter); ?>"</small></p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Block</th>
                <th>Status</th>
                <th>Enrollment Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($students as $student): ?>
                <tr>
                  <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                  <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                  <td><?php echo htmlspecialchars($student['course'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($student['block'] ?? 'N/A'); ?></td>
                  <td>
                    <span class="status-badge status-<?php echo $student['status']; ?>">
                      <?php echo ucfirst($student['status']); ?>
                    </span>
                  </td>
                  <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                  <td>
                    <a href="enrollments.php?view=<?php echo $student['id']; ?>" class="btn-view">View Details</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&course=<?php echo urlencode($course_filter); ?>">« Previous</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
          <?php if ($i == $page): ?>
            <span class="current"><?php echo $i; ?></span>
          <?php else: ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&course=<?php echo urlencode($course_filter); ?>"><?php echo $i; ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
          <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&course=<?php echo urlencode($course_filter); ?>">Next »</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
