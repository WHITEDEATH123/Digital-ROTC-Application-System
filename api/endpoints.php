<?php
/**
 * API Endpoints for Digital ROTC System
 * Handles AJAX requests from JavaScript
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$sessionManager = new SessionManager();
$database = new Database();
$db = $database->getConnection();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'get_enrollments':
            // Admin only - get enrollments by status
            $sessionManager->requireRole('admin');
            $status = isset($_GET['status']) ? $_GET['status'] : 'all';
            
            if ($status === 'all') {
                $query = "SELECT e.*, u.name as student_name FROM enrollments e 
                         JOIN users u ON e.student_id = u.id 
                         ORDER BY e.updated_at DESC";
                $stmt = $db->prepare($query);
                $stmt->execute();
            } else {
                $query = "SELECT e.*, u.name as student_name FROM enrollments e 
                         JOIN users u ON e.student_id = u.id 
                         WHERE e.status = ? 
                         ORDER BY e.updated_at DESC";
                $stmt = $db->prepare($query);
                $stmt->execute([$status]);
            }
            
            $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $enrollments]);
            break;
            
        case 'get_enrollment':
            // Get specific enrollment by ID
            $sessionManager->requireLogin();
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($_SESSION['role'] === 'admin') {
                $query = "SELECT e.*, u.name as student_name FROM enrollments e 
                         JOIN users u ON e.student_id = u.id 
                         WHERE e.id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
            } else {
                // Students can only see their own enrollments
                $query = "SELECT * FROM enrollments WHERE id = ? AND student_id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id, $_SESSION['user_id']]);
            }
            
            $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($enrollment) {
                echo json_encode(['success' => true, 'data' => $enrollment]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
            }
            break;
            
        case 'update_enrollment_status':
            // Admin only - approve/reject enrollment
            $sessionManager->requireRole('admin');
            
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            $status = $input['status'] ?? '';
            $remarks = $input['remarks'] ?? '';
            
            if (!in_array($status, ['approved', 'rejected'])) {
                throw new Exception('Invalid status');
            }
            
            $query = "UPDATE enrollments SET status = ?, admin_remarks = ?, reviewed_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$status, $remarks, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            break;
            
        case 'send_remarks':
            // Admin only - send remarks to student
            $sessionManager->requireRole('admin');
            
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            $remarks = $input['remarks'] ?? '';
            
            $query = "UPDATE enrollments SET admin_remarks = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$remarks, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Remarks sent successfully']);
            break;
            
        case 'get_dashboard_data':
            // Student only - get dashboard information
            $sessionManager->requireRole('student');
            
            $student_id = $_SESSION['user_id'];
            
            // Get user info
            $user_query = "SELECT * FROM users WHERE id = ?";
            $user_stmt = $db->prepare($user_query);
            $user_stmt->execute([$student_id]);
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get enrollment info
            $enrollment_query = "SELECT * FROM enrollments WHERE student_id = ? ORDER BY created_at DESC LIMIT 1";
            $enrollment_stmt = $db->prepare($enrollment_query);
            $enrollment_stmt->execute([$student_id]);
            $enrollment = $enrollment_stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'data' => [
                    'user' => $user,
                    'enrollment' => $enrollment
                ]
            ]);
            break;
            
        case 'delete_enrollment':
            // Admin only - delete enrollment (soft delete by changing status)
            $sessionManager->requireRole('admin');
            
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            
            $query = "DELETE FROM enrollments WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Enrollment deleted successfully']);
            break;
            
        case 'get_statistics':
            // Admin only - get enrollment statistics
            $sessionManager->requireRole('admin');
            
            $stats = [];
            
            // Count by status
            $status_query = "SELECT status, COUNT(*) as count FROM enrollments GROUP BY status";
            $status_stmt = $db->prepare($status_query);
            $status_stmt->execute();
            $status_counts = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($status_counts as $row) {
                $stats[$row['status']] = $row['count'];
            }
            
            // Total students
            $total_query = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
            $total_stmt = $db->prepare($total_query);
            $total_stmt->execute();
            $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_students'] = $total_result['count'];
            
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
