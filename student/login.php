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
        $error_message = 'Please enter both Student ID and password.';
    } else {
        // Check if student exists
        $query = "SELECT * FROM users WHERE id = ? AND role = 'student'";
        $stmt = $db->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $sessionManager->login($user['id'], $user['role']);
            header('Location: dashboard.php');
            exit();
        } else {
            // Check if it's a new student (auto-create account)
            if (!$user) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_query = "INSERT INTO users (id, password, role, name) VALUES (?, ?, 'student', ?)";
                $insert_stmt = $db->prepare($insert_query);
                
                if ($insert_stmt->execute([$username, $hashed_password, $username])) {
                    $sessionManager->login($username, 'student');
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error_message = 'Error creating account. Please try again.';
                }
            } else {
                $error_message = 'Invalid Student ID or password.';
            }
        }
    }
}

// If already logged in as student, redirect
if ($sessionManager->isLoggedIn() && $_SESSION['role'] == 'student') {
    header('Location: dashboard.php');
    exit();
} elseif ($sessionManager->isLoggedIn() && $_SESSION['role'] == 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Student Login - Digital ROTC</title>
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
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.3);
      padding: 40px;
      width: 100%;
      max-width: 400px;
      text-align: center;
      position: relative;
      z-index: 10;
      border: 2px solid rgba(255, 255, 255, 0.8);
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
      border-color: #667eea;
    }
    
    .actions {
      text-align: center;
      margin-top: 30px;
    }
    
    button {
      background: linear-gradient(135deg, #2d5016 0%, #4a7c59 100%);
      color: white;
      padding: 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 500;
      transition: all 0.3s ease;
      width: 100%;
    }
    
    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(45, 80, 22, 0.3);
    }
    
    .info-text {
      margin-top: 25px;
      padding: 15px;
      background-color: #e3f2fd;
      border-radius: 10px;
      font-size: 12px;
      color: #1565c0;
      line-height: 1.4;
    }
    
    .back-link {
      margin-top: 20px;
    }
    
    .back-link a {
      color: #4a7c59;
      text-decoration: none;
      font-size: 14px;
    }
    
    .back-link a:hover {
      text-decoration: underline;
    }
    
    /* Responsive background */
    @media (max-width: 768px) {
      body {
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
      }
      
      .login-container {
        margin: 20px;
        padding: 30px;
        max-width: none;
        width: calc(100% - 40px);
      }
    }
    
    @media (max-width: 480px) {
      body {
        background-attachment: scroll;
      }
      
      .login-container {
        margin: 10px;
        padding: 25px;
        width: calc(100% - 20px);
        backdrop-filter: blur(5px);
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

    <h1>Student Portal</h1>
    <p class="subtitle">Digital ROTC Enrollment System</p>

    <?php if (!empty($error_message)): ?>
      <div class="error-message">
        <?php echo htmlspecialchars($error_message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <label>Student ID
        <input type="text" name="username" placeholder="e.g. 2025-0001" required 
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
      </label>
      <label>Password
        <input type="password" name="password" placeholder="Create or enter your password" required />
      </label>

      <div class="actions">
        <button type="submit">Login / Register</button>
      </div>
    </form>

    <div class="info-text">
      <strong>New Student?</strong><br>
      Enter your Student ID and create a password. Your account will be created automatically.
      <br><br>
      <strong>Existing Student?</strong><br>
      Use your Student ID and the password you created previously.
    </div>
    
    <div class="back-link">
      <a href="../index.php">← Back to Main Page</a>
    </div>
  </div>
</body>
</html>
