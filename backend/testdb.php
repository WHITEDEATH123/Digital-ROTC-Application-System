<?php
include "db.php"; // your existing db.php

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed: " . $conn->connect_error]);
} else {
    echo json_encode(["success" => true, "message" => "DB connected!"]);
}
?>
