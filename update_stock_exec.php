<?php
header('Content-Type: application/json');
require_once 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['id']) && isset($data['stock'])) {
    $stmt = $pdo->prepare("UPDATE items SET stock_quantity = ? WHERE item_id = ?");
    $success = $stmt->execute([$data['stock'], $data['id']]);
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false, 'message' => 'データ不足']);
}