<?php
// backend/enroll.php
header("Content-Type: application/json");
include "db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success"=>false, "message"=>"Invalid request method."]);
    exit;
}

$student_id = $_POST['student_id'] ?? '';
if (!$student_id) {
    echo json_encode(["success"=>false,"message"=>"Missing student ID."]);
    exit;
}

// Check if cadet exists
$stmt = $conn->prepare("SELECT cadet_id FROM Cadet WHERE cadet_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$cadet = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cadet) {
    echo json_encode(["success"=>false,"message"=>"Cadet not found."]);
    exit;
}

// Insert or update EnrolForm
$fields = ['fname','mname','lname','age','religion','dob','pob','height','weight','complexion','bloodtype','block','course','army','address','phone','email','father','foccupation','mother','moccupation','emergencyPerson','relationship','emergencyContact','ms','semester','schoolyear','grade','advanceCourse','photo'];
$values = [];
$types = '';
foreach ($fields as $f) {
    $values[] = $_POST[$f] ?? null;
    $types .= ($f === "age" || $f === "advanceCourse") ? 'i' : 's';
}

// Check if form exists
$stmt = $conn->prepare("SELECT form_id FROM EnrolForm WHERE cadet_id = ?");
$stmt->bind_param("i",$student_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    // Update
    $set = implode("=?,", $fields) . "=?";
    $stmt = $conn->prepare("UPDATE EnrolForm SET $set, status='submitted', updated_at=NOW() WHERE cadet_id=?");
    $values[] = $student_id;
    $types .= 'i';
} else {
    // Insert
    $placeholders = implode(",", array_fill(0,count($fields),'?'));
    $stmt = $conn->prepare("INSERT INTO EnrolForm (".implode(",", $fields).", cadet_id) VALUES ($placeholders, ?)");
    $values[] = $student_id;
    $types .= 'i';
}

$stmt->bind_param($types, ...$values);
if ($stmt->execute()) echo json_encode(["success"=>true,"message"=>"Enrollment submitted successfully."]);
else echo json_encode(["success"=>false,"message"=>"Failed to submit enrollment: ".$stmt->error]);

$stmt->close();
$conn->close();
?>
