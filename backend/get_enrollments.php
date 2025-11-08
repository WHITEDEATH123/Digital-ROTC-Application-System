<?php
// backend/get_enrollment.php
header("Content-Type: application/json");
include "db.php";

$id = $_GET['id'] ?? '';
if (!$id) {
    echo json_encode(["success"=>false,"message"=>"Missing cadet ID"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM EnrolForm WHERE cadet_id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$enrol = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($enrol) {
    if (!empty($enrol['photo'])) $enrol['photo'] = "./uploads/" . basename($enrol['photo']);
    echo json_encode(["success"=>true,"enrollment"=>$enrol]);
} else {
    echo json_encode(["success"=>false,"message"=>"Enrollment not found"]);
}
$conn->close();
?>
