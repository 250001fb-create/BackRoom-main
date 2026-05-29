<?php
// get_sale_details.php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$sale_id = filter_input(INPUT_GET, 'sale_id', FILTER_VALIDATE_INT);

if (!$sale_id) {
    echo json_encode([]);
    exit;
}

try {
    // 【SQLから正確に抽出】
    // sale_detailsのitem_idと、itemsのitem_idを結合し、selling_priceを取得します
    $sql = "SELECT sd.quantity, sd.selling_price, i.item_name 
            FROM sale_details sd
            JOIN items i ON sd.item_id = i.item_id
            WHERE sd.sale_id = ?
            ORDER BY sd.detail_id ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sale_id]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formatted_details = [];
    foreach ($details as $row) {
        $qty = (int)$row['quantity'];
        $price = (int)$row['selling_price']; // 保存されている販売単価

        $formatted_details[] = [
            'item_name' => $row['item_name'],
            'quantity'  => $qty,
            'price'     => $price,
            'subtotal'  => $price * $qty // 単価 × 数量 で小計を算出
        ];
    }

    echo json_encode($formatted_details);
} catch (PDOException $e) {
    echo json_encode([['item_name' => 'エラー: ' . $e->getMessage(), 'price' => 0, 'quantity' => 0, 'subtotal' => 0]]);
}