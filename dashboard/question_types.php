<?php
// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../db_connect.php';

// Query question types
$sql = "SELECT id, type_name, description FROM question_types ORDER BY id ASC";
$result = $conn->query($sql);

$types = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $types[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "data" => $types
]);

$conn->close();