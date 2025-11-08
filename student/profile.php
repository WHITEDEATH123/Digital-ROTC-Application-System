<?php
require_once '../config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('student');

$database = new Database();
$db = $database->getConnection();

$current_user = $sessionManager->getCurrentUser();
$student_id = $_SESSION['user_id'];
$message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate current password if changing password
        if (!empty($new_password)) {
            if (empty($current_password)) {
                throw new Exception('Current password is required to change password.');
            }
            
            if (!password_verify($current_password, $current_user['password'])) {
                throw new Exception('Current password is incorrect.');
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception('New passwords do not match.');
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception('New password must be at least 6 characters long.');
            }
            
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET name = ?, email = ?, password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($update_query);
            $stmt->execute([$name, $email, $hashed_password, $student_id]);
        } else {
            // Update without password change
            $update_query = "UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($update_query);
            $stmt->execute([$name, $email, $student_id]);
        }
        
        $message = 'Profile updated successfully!';
        
        // Refresh user data
        $user_query = "SELECT * FROM users WHERE id = ?";
        $user_stmt = $db->prepare($user_query);
        $user_stmt->execute([$student_id]);
        $current_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get enrollment history
$history_query = "SELECT * FROM enrollments WHERE student_id = ? ORDER BY created_at DESC";
$history_stmt = $db->prepare($history_query);
$history_stmt->execute([$student_id]);
$enrollment_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>My Profile - Student Portal</title>
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
    
    .nav-link:hover, .nav-link.active {
      background-color: rgba(255,255,255,0.2);
    }
    
    .container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .profile-header {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      text-align: center;
    }
    
    .profile-avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      color: white;
      margin: 0 auto 1rem;
    }
    
    .profile-name {
      font-size: 1.8rem;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .profile-id {
      color: #666;
      font-size: 1.1rem;
    }
    
    .content-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
    }
    
    .card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .card-title {
      font-size: 1.3rem;
      color: #333;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #333;
    }
    
    .form-group input {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e1e5e9;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.3s ease;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: #667eea;
    }
    
    .btn {
      display: inline-block;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      font-size: 16px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 1.5rem;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .history-item {
      padding: 1rem;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .history-item:last-child {
      border-bottom: none;
    }
    
    .history-info h4 {
      color: #333;
      margin-bottom: 0.25rem;
    }
    
    .history-info p {
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
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    
    .no-history {
      text-align: center;
      color: #666;
      padding: 2rem;
      font-style: italic;
    }
    
    .password-section {
      border-top: 1px solid #eee;
      padding-top: 1.5rem;
      margin-top: 1.5rem;
    }
    
    .password-toggle {
      background: none;
      border: none;
      color: #667eea;
      cursor: pointer;
      text-decoration: underline;
      font-size: 14px;
    }
    
    .password-fields {
      display: none;
      margin-top: 1rem;
    }
    
    .password-fields.show {
      display: block;
    }
    
    @media (max-width: 768px) {
      .content-grid {
        grid-template-columns: 1fr;
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
        <div style="font-size: 1.2rem;">Student Portal</div>
        <div style="font-size: 0.8rem; opacity: 0.9;">Digital ROTC System</div>
      </div>
    </div>
    <div class="navbar-nav">
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="profile.php" class="nav-link active">Profile</a>
      <a href="../index.php" class="nav-link">Logout</a>
    </div>
  </nav>

  <div class="container">
    <!-- Profile Header -->
    <div class="profile-header">
      <div class="profile-avatar">👤</div>
      <h1 class="profile-name"><?php echo htmlspecialchars($current_user['name'] ?: 'Student'); ?></h1>
      <p class="profile-id">Student ID: <?php echo htmlspecialchars($student_id); ?></p>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
      <!-- Profile Settings -->
      <div class="card">
        <h2 class="card-title">
          <span>⚙️</span>
          Profile Settings
        </h2>

        <?php if (!empty($message)): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($current_user['name'] ?: ''); ?>" required>
          </div>

          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($current_user['email'] ?: ''); ?>">
          </div>

          <div class="form-group">
            <label>Student ID</label>
            <input type="text" value="<?php echo htmlspecialchars($student_id); ?>" readonly style="background-color: #f8f9fa;">
          </div>

          <div class="password-section">
            <button type="button" class="password-toggle" onclick="togglePasswordFields()">
              Change Password
            </button>
            
            <div class="password-fields" id="passwordFields">
              <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password">
              </div>

              <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" minlength="6">
              </div>

              <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" minlength="6">
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Update Profile</button>
          <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
      </div>

      <!-- Enrollment History -->
      <div class="card">
        <h2 class="card-title">
          <span>📋</span>
          Enrollment History
        </h2>

        <?php if (empty($enrollment_history)): ?>
          <div class="no-history">No enrollment history found.</div>
        <?php else: ?>
          <?php foreach ($enrollment_history as $enrollment): ?>
            <div class="history-item">
              <div class="history-info">
                <h4>ROTC Enrollment</h4>
                <p><?php echo htmlspecialchars($enrollment['course']); ?> - Block <?php echo htmlspecialchars($enrollment['block']); ?></p>
                <p>Submitted: <?php echo date('M j, Y', strtotime($enrollment['created_at'])); ?></p>
              </div>
              <span class="status-badge status-<?php echo $enrollment['status']; ?>">
                <?php echo ucfirst($enrollment['status']); ?>
              </span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top: 1.5rem; text-align: center;">
          <a href="enroll.php" class="btn btn-primary">New Enrollment</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePasswordFields() {
      const fields = document.getElementById('passwordFields');
      const toggle = document.querySelector('.password-toggle');
      
      if (fields.classList.contains('show')) {
        fields.classList.remove('show');
        toggle.textContent = 'Change Password';
        // Clear password fields
        fields.querySelectorAll('input').forEach(input => input.value = '');
      } else {
        fields.classList.add('show');
        toggle.textContent = 'Cancel Password Change';
      }
    }
  </script>
</body>
</html>
