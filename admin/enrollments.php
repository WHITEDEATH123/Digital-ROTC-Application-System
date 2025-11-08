<?php
require_once '../config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('admin');

$database = new Database();
$db = $database->getConnection();

$current_user = $sessionManager->getCurrentUser();

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selected_ids = $_POST['selected_enrollments'] ?? [];
    
    if (!empty($selected_ids) && in_array($action, ['approve', 'reject'])) {
        $status = $action == 'approve' ? 'approved' : 'rejected';
        $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
        
        $bulk_query = "UPDATE enrollments SET status = ?, reviewed_at = NOW() WHERE id IN ($placeholders)";
        $stmt = $db->prepare($bulk_query);
        $stmt->execute(array_merge([$status], $selected_ids));
        
        $message = ucfirst($action) . 'd ' . count($selected_ids) . ' enrollment(s) successfully!';
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$course_filter = $_GET['course'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($status_filter != 'all') {
    $where_conditions[] = "e.status = ?";
    $params[] = $status_filter;
}

if ($course_filter != 'all') {
    $where_conditions[] = "e.course = ?";
    $params[] = $course_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.student_id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get enrollments with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$count_query = "SELECT COUNT(*) as total FROM enrollments e JOIN users u ON e.student_id = u.id $where_clause";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

$enrollments_query = "SELECT e.*, u.name as student_name 
                     FROM enrollments e 
                     JOIN users u ON e.student_id = u.id 
                     $where_clause
                     ORDER BY e.updated_at DESC 
                     LIMIT $per_page OFFSET $offset";
$enrollments_stmt = $db->prepare($enrollments_query);
$enrollments_stmt->execute($params);
$enrollments = $enrollments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available courses for filter
$courses_query = "SELECT DISTINCT course FROM enrollments WHERE course IS NOT NULL ORDER BY course";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_COLUMN);

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
  <title>Manage Enrollments - Admin Portal</title>
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
      padding: 2rem;
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
      font-weight: 500;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .filter-group select,
    .filter-group input {
      padding: 10px 12px;
      border: 2px solid #e1e5e9;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s;
    }
    
    .filter-group select:focus,
    .filter-group input:focus {
      outline: none;
      border-color: #ff6b6b;
    }
    
    .btn {
      display: inline-block;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 14px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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
    
    .btn-danger {
      background: #dc3545;
      color: white;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .enrollments-section {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }
    
    .section-title {
      font-size: 1.3rem;
      color: #333;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .bulk-actions {
      display: flex;
      gap: 1rem;
      align-items: center;
    }
    
    .table-container {
      overflow-x: auto;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2rem;
    }
    
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    
    th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #333;
      position: sticky;
      top: 0;
    }
    
    tr:hover {
      background-color: #f8f9fa;
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
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    
    .action-buttons {
      display: flex;
      gap: 5px;
    }
    
    .btn-sm {
      padding: 5px 10px;
      font-size: 12px;
    }
    
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      margin-top: 2rem;
    }
    
    .pagination a,
    .pagination span {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      text-decoration: none;
      color: #333;
    }
    
    .pagination a:hover {
      background-color: #f8f9fa;
    }
    
    .pagination .current {
      background-color: #ff6b6b;
      color: white;
      border-color: #ff6b6b;
    }
    
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .no-results {
      text-align: center;
      color: #666;
      padding: 3rem;
      font-style: italic;
    }
    
    .checkbox-column {
      width: 40px;
    }
    
    .select-all {
      cursor: pointer;
    }
    
    @media (max-width: 768px) {
      .navbar {
        padding: 1rem;
      }
      
      .container {
        padding: 1rem;
      }
      
      .filters-grid {
        grid-template-columns: 1fr;
      }
      
      .section-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }
      
      .bulk-actions {
        justify-content: center;
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
      <a href="enrollments.php" class="nav-link active">Enrollments</a>
      <a href="students.php" class="nav-link">Students</a>
      <a href="reports.php" class="nav-link">Reports</a>
      <form method="POST" style="display: inline;">
        <button type="submit" name="logout" class="btn-logout">Logout</button>
      </form>
    </div>
  </nav>

  <div class="container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Enrollment Management</h1>
      <p class="page-subtitle">Review, approve, and manage student ROTC enrollments</p>
    </div>

    <!-- Success Message -->
    <?php if (isset($message)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Filters Section -->
    <div class="filters-section">
      <form method="GET" class="filters-grid">
        <div class="filter-group">
          <label>Status Filter</label>
          <select name="status">
            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Statuses</option>
            <option value="submitted" <?php echo $status_filter == 'submitted' ? 'selected' : ''; ?>>Submitted</option>
            <option value="resubmitted" <?php echo $status_filter == 'resubmitted' ? 'selected' : ''; ?>>Resubmitted</option>
            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Course Filter</label>
          <select name="course">
            <option value="all" <?php echo $course_filter == 'all' ? 'selected' : ''; ?>>All Courses</option>
            <?php foreach ($courses as $course): ?>
              <option value="<?php echo htmlspecialchars($course); ?>" <?php echo $course_filter == $course ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($course); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label>Search</label>
          <input type="text" name="search" placeholder="Name or Student ID" value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div class="filter-group">
          <button type="submit" class="btn btn-primary">Apply Filters</button>
        </div>
      </form>
    </div>

    <!-- Enrollments Section -->
    <div class="enrollments-section">
      <div class="section-header">
        <h2 class="section-title">
          <span>📋</span>
          Enrollments (<?php echo $total_records; ?> total)
        </h2>
        
        <div class="bulk-actions">
          <form method="POST" id="bulkForm">
            <select name="bulk_action" id="bulkAction">
              <option value="">Bulk Actions</option>
              <option value="approve">Approve Selected</option>
              <option value="reject">Reject Selected</option>
            </select>
            <button type="submit" class="btn btn-secondary" onclick="return confirmBulkAction()">Apply</button>
          </form>
        </div>
      </div>

      <?php if (empty($enrollments)): ?>
        <div class="no-results">
          No enrollments found matching your criteria.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th class="checkbox-column">
                  <input type="checkbox" class="select-all" onchange="toggleAllCheckboxes(this)">
                </th>
                <th>Student</th>
                <th>Course</th>
                <th>Block</th>
                <th>NSTP</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($enrollments as $enrollment): ?>
                <tr>
                  <td>
                    <input type="checkbox" name="selected_enrollments[]" value="<?php echo $enrollment['id']; ?>" form="bulkForm">
                  </td>
                  <td>
                    <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($enrollment['student_id']); ?></small>
                  </td>
                  <td><?php echo htmlspecialchars($enrollment['course']); ?></td>
                  <td>Block <?php echo htmlspecialchars($enrollment['block']); ?></td>
                  <td><?php echo htmlspecialchars($enrollment['army_nstp']); ?></td>
                  <td>
                    <span class="status-badge status-<?php echo $enrollment['status']; ?>">
                      <?php echo ucfirst($enrollment['status']); ?>
                    </span>
                  </td>
                  <td>
                    <?php echo date('M j, Y', strtotime($enrollment['submitted_at'] ?: $enrollment['created_at'])); ?>
                    <?php if ($enrollment['resubmitted_at']): ?>
                      <br><small>Resubmitted: <?php echo date('M j', strtotime($enrollment['resubmitted_at'])); ?></small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <a href="../view.php?id=<?php echo $enrollment['id']; ?>" class="btn btn-primary btn-sm">View</a>
                      <?php if (in_array($enrollment['status'], ['submitted', 'resubmitted'])): ?>
                        <button onclick="quickApprove(<?php echo $enrollment['id']; ?>)" class="btn btn-success btn-sm">Approve</button>
                        <button onclick="quickReject(<?php echo $enrollment['id']; ?>)" class="btn btn-danger btn-sm">Reject</button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
          <div class="pagination">
            <?php if ($page > 1): ?>
              <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">← Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
              <?php if ($i == $page): ?>
                <span class="current"><?php echo $i; ?></span>
              <?php else: ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
              <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next →</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function toggleAllCheckboxes(source) {
      const checkboxes = document.querySelectorAll('input[name="selected_enrollments[]"]');
      checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
      });
    }

    function confirmBulkAction() {
      const action = document.getElementById('bulkAction').value;
      const selected = document.querySelectorAll('input[name="selected_enrollments[]"]:checked');
      
      if (!action) {
        alert('Please select an action.');
        return false;
      }
      
      if (selected.length === 0) {
        alert('Please select at least one enrollment.');
        return false;
      }
      
      return confirm(`Are you sure you want to ${action} ${selected.length} enrollment(s)?`);
    }

    function quickApprove(id) {
      if (confirm('Are you sure you want to approve this enrollment?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
          <input type="hidden" name="bulk_action" value="approve">
          <input type="hidden" name="selected_enrollments[]" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    }

    function quickReject(id) {
      if (confirm('Are you sure you want to reject this enrollment?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
          <input type="hidden" name="bulk_action" value="reject">
          <input type="hidden" name="selected_enrollments[]" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    }
  </script>
</body>
</html>
