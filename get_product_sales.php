<?php
header('Content-Type: application/json; charset=UTF-8');

// データベース接続設定（環境に合わせて変更してください）
$dsn = 'mysql:dbname=tas_system;host=localhost;charset=utf8mb4';
$user = 'root';
$password = ''; // パスワードを設定している場合は入力

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB接続失敗: ' . $e->getMessage()]);
    exit;
}

// パラメータの取得
$search_type = $_GET['type'] ?? '';
$keyword = $_GET['keyword'] ?? '';
$period = $_GET['period'] ?? '本日';

if (!$keyword) {
    echo json_encode(['error' => 'キーワードが空です']);
    exit;
}

// 1. まず該当する商品を検索
$item_sql = "SELECT item_id, item_name, category FROM items WHERE ";
if ($search_type === 'id') {
    $item_sql .= "item_id = :keyword";
} elseif ($search_type === 'barcode') {
    $item_sql .= "barcode = :keyword";
} else {
    $item_sql .= "item_name LIKE :keyword_like";
}
$item_sql .= " LIMIT 1";

$stmt = $pdo->prepare($item_sql);
if ($search_type === 'name') {
    $stmt->bindValue(':keyword_like', '%' . $keyword . '%', PDO::PARAM_STR);
} else {
    $stmt->bindValue(':keyword', $keyword, PDO::PARAM_STR);
}
$stmt->execute();
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => '商品が見つかりませんでした。']);
    exit;
}

// 2. 期間に応じたSQLのWHERE句（日付条件）を作成
$date_condition = "";
switch ($period) {
    case '過去1時間':
        $date_condition = "AND s.created_at >= NOW() - INTERVAL 1 HOUR";
        break;
    case '本日':
        $date_condition = "AND DATE(s.created_at) = CURDATE()";
        break;
    case '今週':
        $date_condition = "AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
        break;
    case '今月':
        $date_condition = "AND s.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        break;
    case '今年':
        $date_condition = "AND s.created_at >= DATE_FORMAT(CURDATE(), '%Y-01-01')";
        break;
    default:
        $date_condition = "AND DATE(s.created_at) = CURDATE()";
}

// 3. 該当商品の売上個数と売上金額の合計を計算
$sales_sql = "
    SELECT 
        COALESCE(SUM(sd.quantity), 0) AS total_count,
        COALESCE(SUM(sd.quantity * sd.selling_price), 0) AS total_amount
    FROM sale_details sd
    JOIN sales s ON sd.sale_id = s.sale_id
    WHERE sd.item_id = :item_id " . $date_condition;

$sales_stmt = $pdo->prepare($sales_sql);
$sales_stmt->bindValue(':item_id', $product['item_id'], PDO::PARAM_INT);
$sales_stmt->execute();
$sales_data = $sales_stmt->fetch();

// レスポンスを返却
echo json_encode([
    'success' => true,
    'product' => [
        'id' => sprintf('%05d', $product['item_id']), // 5桁埋め表記
        'name' => $product['item_name'],
        'category' => $product['category']
    ],
    'sales' => [
        'count' => number_format($sales_data['total_count']) . '個',
        'amount' => number_format($sales_data['total_amount']) . '円'
    ]
]);