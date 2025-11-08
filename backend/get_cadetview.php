<?php
header("Content-Type: application/json");
include "db.php";

$cadet_id = $_GET['id'] ?? '';
if (!$cadet_id) {
    echo json_encode(["success"=>false,"message"=>"Missing cadet ID"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT c.cadet_id, c.student_id, e.*, 
           (SELECT GROUP_CONCAT(r.remark_text SEPARATOR '; ') 
            FROM Remarks r WHERE r.form_id=e.form_id) AS remarks_text
    FROM Cadet c
    LEFT JOIN EnrolForm e ON c.cadet_id=e.cadet_id
    WHERE c.cadet_id=?
");
$stmt->bind_param("i",$cadet_id);
$stmt->execute();
$cadet = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($cadet) {
    if (!empty($cadet['photo'])) $cadet['photo'] = "./uploads/" . basename($cadet['photo']);
    echo json_encode(["success"=>true,"cadet"=>$cadet]);
} else {
    echo json_encode(["success"=>false,"message"=>"Cadet not found"]);
}

$conn->close();
?>
