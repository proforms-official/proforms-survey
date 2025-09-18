<?php
// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET'); 
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db_connect.php';

$response = ['status' => 'error', 'data' => []];

try {
    $stmt = $pdo->prepare("SELECT question_uuid, type_name, description FROM question_types ORDER BY id ASC");
    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['status'] = 'success';
    $response['data'] = $types;
} catch (PDOException $e) {
    http_response_code(500);
    $response['status'] = 'error';
    $response['message'] = 'Failed to fetch question types';
    $response['debug'] = $e->getMessage();
}

echo json_encode($response);