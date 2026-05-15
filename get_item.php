<?php
header('Content-Type: application/json');
require_once 'db.php';
$id = $_GET['id'] ?? '';
$stmt = $pdo->prepare("SELECT item_name, stock_quantity FROM items WHERE item_id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode(['success' => (bool)$item, 'item' => $item]);