<?php
// 既存の接続ファイルを読み込みます
require_once 'db.php';

// 検索された商品IDを取得
$search_id = isset($_GET['search_id']) ? trim($_GET['search_id']) : '';

try {
    if ($search_id !== '') {
        // 商品IDが入力されている場合は、その商品だけを検索
        $stmt = $pdo->prepare('SELECT * FROM items WHERE item_id = :item_id');
        $stmt->execute(['item_id' => $search_id]);
    } else {
        // 入力されていない場合は全件表示
        $stmt = $pdo->query('SELECT * FROM items');
    }
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('データ取得失敗: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫確認 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/stock_page.css">
</head>
<body>
    <div class="main-container">
        <div class="top-section">
            <a href="stock_select.php" class="btn-back">戻る</a>
            
            <form action="stock.php" method="GET" class="search-container">
                <input type="text" name="search_id" value="<?php echo htmlspecialchars($search_id, ENT_QUOTES, 'UTF-8'); ?>" placeholder="商品IDの入力" class="search-input">
                <button type="submit" class="btn-search">検索</button>
            </form>
        </div>
        
        <div class="table-container">
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th>商品ID</th>
                        <th>個数</th>
                        <th>ジャンル</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">該当する商品がありません。</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['item_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['stock_quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>