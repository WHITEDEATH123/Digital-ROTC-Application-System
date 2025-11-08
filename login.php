<?php
require_once 'config/database.php';

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
        // Check if user exists
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $sessionManager->login($user['id'], $user['role']);
            
            if ($user['role'] == 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: dashboard.php');
            }
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
                $error_message = 'Invalid username or password.';
            }
        }
    }
}

// If already logged in, redirect
if ($sessionManager->isLoggedIn()) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Digital ROTC - Login</title>
  <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body>
  <div class="login-container">
    <!-- JHCSC Logo -->
    <div class="logo-container">
      <img src="assets/img/jhcsc-logo.png" alt="JHCSC Logo" class="logo">
    </div>

    <h1>Digital ROTC Application</h1>

    <?php if (!empty($error_message)): ?>
      <div class="error-message" style="color: red; margin-bottom: 15px; text-align: center;">
        <?php echo htmlspecialchars($error_message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <label>Username / Student ID
        <input type="text" name="username" placeholder="e.g. 2025-0001" required 
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
      </label>
      <label>Password
        <input type="password" name="password" placeholder="password" required />
      </label>

      <div class="actions">
        <button type="submit">Log In</button>
      </div>
    </form>

    <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
      <p>Default Admin: admin / admin123</p>
      <p>Students: Use your Student ID as username. New accounts will be created automatically.</p>
    </div>
  </div>
</body>
</html>
