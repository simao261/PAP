<?php
require_once '../includes/config.php';
require_once '../includes/recommendation.php';

header('Content-Type: application/json');

$usage = $_GET['usage'] ?? '';
$budget = $_GET['budget'] ?? null;

if (empty($usage)) {
    http_response_code(400);
    echo json_encode(['error' => 'Uso não especificado']);
    exit;
}

$recommendations = getBuildRecommendation($usage, $budget);
echo json_encode($recommendations);
?>
