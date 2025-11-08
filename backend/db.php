<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

include "db.php";

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (!$username || !$password) {
    echo json_encode(['success'=>false,'message'=>'Username and password required.']);
    exit;
}

// Admin login
$stmt = $conn->prepare("SELECT admin_id, username, password FROM Admin WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($admin) {
    if ($password === $admin['password']) {
        echo json_encode(['success'=>true,'user'=>[
            'id'=>$admin['admin_id'],
            'role'=>'admin',
            'username'=>$admin['username']
        ]]);
        exit;
    } else {
        echo json_encode(['success'=>false,'message'=>'Incorrect password.']);
        exit;
    }
}

// Cadet login
$stmt = $conn->prepare("SELECT cadet_id, student_id, password FROM Cadet WHERE student_id=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$cadet = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($cadet) {
    if ($password === $cadet['password']) {
        echo json_encode(['success'=>true,'user'=>[
            'id'=>$cadet['cadet_id'],
            'role'=>'student',
            'username'=>$cadet['student_id']
        ]]);
        exit;
    } else {
        echo json_encode(['success'=>false,'message'=>'Incorrect password.']);
        exit;
    }
}

echo json_encode(['success'=>false,'message'=>'User not found.']);
?>
