<?php
/**
 * Database Configuration for Digital ROTC System
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'rotc_system';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

/**
 * Session Management Class
 */
class SessionManager {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Start session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login($user_id, $role) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;
        $_SESSION['login_time'] = time();
        
        // Store session in database
        $session_id = session_id();
        $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        $query = "INSERT INTO sessions (id, user_id, data, expires_at) VALUES (?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE data = VALUES(data), expires_at = VALUES(expires_at)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$session_id, $user_id, serialize($_SESSION), $expires_at]);
    }
    
    public function logout() {
        $session_id = session_id();
        
        // Remove from database
        $query = "DELETE FROM sessions WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$session_id]);
        
        // Destroy session
        session_destroy();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            // Redirect to appropriate login based on current path
            $current_path = $_SERVER['REQUEST_URI'];
            $script_name = $_SERVER['SCRIPT_NAME'];
            
            if (strpos($current_path, '/admin/') !== false || strpos($script_name, '/admin/') !== false || strpos($current_path, 'admin.php') !== false) {
                // If already in admin directory, use relative path
                if (strpos($script_name, '/admin/') !== false) {
                    header('Location: login.php');
                } else {
                    header('Location: admin/login.php');
                }
            } else {
                // If already in student directory, use relative path
                if (strpos($script_name, '/student/') !== false) {
                    header('Location: login.php');
                } else {
                    header('Location: student/login.php');
                }
            }
            exit();
        }
    }
    
    public function requireRole($role) {
        $this->requireLogin();
        if ($_SESSION['role'] !== $role) {
            // Redirect to appropriate login page based on required role
            $script_name = $_SERVER['SCRIPT_NAME'];
            
            if ($role === 'admin') {
                // If already in admin directory, use relative path
                if (strpos($script_name, '/admin/') !== false) {
                    header('Location: login.php');
                } else {
                    header('Location: admin/login.php');
                }
            } else {
                // If already in student directory, use relative path
                if (strpos($script_name, '/student/') !== false) {
                    header('Location: login.php');
                } else {
                    header('Location: student/login.php');
                }
            }
            exit();
        }
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function cleanExpiredSessions() {
        $query = "DELETE FROM sessions WHERE expires_at < NOW()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
    }
}

/**
 * Utility Functions
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function uploadFile($file, $uploadDir = 'uploads/photos/') {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    if ($file['size'] > 5000000) { // 5MB limit
        throw new RuntimeException('Exceeded filesize limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($file['tmp_name']),
        array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = sprintf('%s.%s', sha1_file($file['tmp_name']), $ext);
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $filepath;
}
?>
