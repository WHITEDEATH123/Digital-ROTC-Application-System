<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$student_id = $data["cadet_id"] ?? '';
$status = $data["status"] ?? '';
$remarks = $data["remarks"] ?? '';

if (!$student_id || !$status) { echo json_encode(["success"=>false,"message"=>"Missing data"]); exit; }

$stmt = $conn->prepare("UPDATE EnrolForm SET status=?, remarks=? WHERE cadet_id=(SELECT cadet_id FROM Cadet WHERE student_id=?)");
$stmt->bind_param("sss",$status,$remarks,$student_id);

if ($stmt->execute()) {
    echo json_encode(["success"=>true,"message"=>"Status updated."]);
} else {
    echo json_encode(["success"=>false,"message"=>"Update failed: ".$stmt->error]);
}

$stmt->close();
$conn->close();
?>
