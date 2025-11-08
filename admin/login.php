<?php
require_once '../config/database.php';

$sessionManager = new SessionManager();
$database = new Database();
$db = $database->getConnection();

$error_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // Check admin credentials only
        $query = "SELECT * FROM users WHERE id = ? AND role = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $sessionManager->login($user['id'], $user['role']);
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = 'Invalid administrator credentials.';
        }
    }
}

// If already logged in as admin, redirect
if ($sessionManager->isLoggedIn() && $_SESSION['role'] == 'admin') {
    header('Location: dashboard.php');
    exit();
} elseif ($sessionManager->isLoggedIn() && $_SESSION['role'] == 'student') {
    header('Location: ../student/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Administrator Login - Digital ROTC</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: url('../assets/img/ro.png') center/cover no-repeat;
      background-attachment: fixed;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    
    /* Removed overlay for 100% background opacity */
    
    .login-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.3);
      padding: 40px;
      width: 100%;
      max-width: 420px;
      text-align: center;
      position: relative;
      z-index: 10;
      border: 2px solid rgba(255, 255, 255, 0.2);
    }
    
    .login-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, #ff9a9e 0%, #fecfef 100%);
    }
    
    .logo-container {
      margin-bottom: 30px;
    }
    
    .logo {
      width: 80px;
      height: 80px;
      margin: 0 auto;
      background: white;
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: 2px solid #f0f0f0;
    }
    
    .logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    
    h1 {
      color: #333;
      margin-bottom: 10px;
      font-size: 1.8rem;
    }
    
    .subtitle {
      color: #666;
      margin-bottom: 30px;
      font-size: 0.9rem;
    }
    
    .error-message {
      background-color: #f8d7da;
      color: #721c24;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
    }
    
    form {
      text-align: left;
    }
    
    label {
      display: block;
      margin-bottom: 20px;
      font-weight: 500;
      color: #333;
    }
    
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e1e5e9;
      border-radius: 10px;
      font-size: 16px;
      margin-top: 5px;
      transition: border-color 0.3s ease;
    }
    
    input[type="text"]:focus, input[type="password"]:focus {
      outline: none;
      border-color: #ff6b6b;
    }
    
    .actions {
      text-align: center;
      margin-top: 30px;
    }
    
    button {
      background: linear-gradient(135deg, #1b5e20 0%, #388e3c 100%);
      color: white;
      padding: 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 500;
      transition: all 0.3s ease;
      width: 100%;
      box-shadow: 0 5px 15px rgba(27, 94, 32, 0.3);
    }
    
    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(27, 94, 32, 0.4);
    }
    
    .security-notice {
      margin-top: 25px;
      padding: 15px;
      background-color: #fff3cd;
      border-radius: 10px;
      font-size: 12px;
      color: #856404;
      line-height: 1.4;
      border: 1px solid #ffeaa7;
    }
    
    .back-link {
      margin-top: 20px;
    }
    
    .back-link a {
      color: #ff6b6b;
      text-decoration: none;
      font-size: 14px;
    }
    
    .back-link a:hover {
      text-decoration: underline;
    }
    
    .admin-features {
      margin-top: 20px;
      text-align: left;
      font-size: 12px;
      color: #666;
    }
    
    .admin-features ul {
      list-style: none;
      padding: 0;
    }
    
    .admin-features li {
      padding: 5px 0;
      position: relative;
      padding-left: 20px;
    }
    
    .admin-features li::before {
      content: '✓';
      position: absolute;
      left: 0;
      color: #28a745;
      font-weight: bold;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      body {
        background-size: cover;
        background-position: center;
        padding: 20px;
      }
      
      .login-container {
        max-width: 100%;
        padding: 30px 20px;
        margin: 10px;
      }
      
      .logo img {
        width: 60px;
        height: 60px;
      }
      
      h1 {
        font-size: 1.8rem;
      }
      
      .subtitle {
        font-size: 0.9rem;
      }
      
      input {
        font-size: 16px; /* Prevents zoom on iOS */
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <!-- Logo -->
    <div class="logo-container">
      <div class="logo">
        <img src="../assets/img/jhcsc-logo.png" alt="JHCSC Logo">
      </div>
    </div>

    <h1>Administrator Portal</h1>
    <p class="subtitle">Digital ROTC Management System</p>

    <?php if (!empty($error_message)): ?>
      <div class="error-message">
        <?php echo htmlspecialchars($error_message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <label>Administrator Username
        <input type="text" name="username" placeholder="Enter admin username" required 
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
      </label>
      <label>Password
        <input type="password" name="password" placeholder="Enter admin password" required />
      </label>

      <div class="actions">
        <button type="submit">Access Admin Panel</button>
      </div>
    </form>

    <div class="admin-features">
      <strong>Administrator Features:</strong>
      <ul>
        <li>Review and manage student enrollments</li>
        <li>Approve or reject applications</li>
        <li>Send remarks and feedback</li>
        <li>Generate reports and statistics</li>
        <li>Manage system users and settings</li>
      </ul>
    </div>

    <div class="security-notice">
      <strong>🔒 Security Notice:</strong><br>
      This is a restricted area for authorized administrators only. All access attempts are logged and monitored.
    </div>
    
    <div class="back-link">
      <a href="../index.php">← Back to Main Page</a>
    </div>
  </div>
</body>
</html>
