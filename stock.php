<?php
// 既存の接続ファイルを読み込みます
require_once 'db.php';

// 検索タイプ（id または name）と 検索キーワードを取得
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'id';
$search_keyword = isset($_GET['search_keyword']) ? trim($_GET['search_keyword']) : '';

try {
    if ($search_keyword !== '') {
        if ($search_type === 'name') {
            // 【商品名検索】入力した文字が含まれるものを検索（部分一致）
            $stmt = $pdo->prepare('SELECT * FROM items WHERE item_name LIKE :keyword');
            $stmt->execute(['keyword' => '%' . $search_keyword . '%']);
        } else {
            // 【商品ID検索】完全に一致するものを検索（完全一致）
            $stmt = $pdo->prepare('SELECT * FROM items WHERE item_id = :keyword');
            $stmt->execute(['keyword' => $search_keyword]);
        }
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
    </style>
</head>
<body>
    <div class="main-container">
        <div class="top-section">
            <a href="stock_select.php" class="btn-back">戻る</a>
            
            <form action="stock.php" method="GET" class="search-container">
                <select name="search_type" class="search-select">
                    <option value="id" <?php if ($search_type === 'id') echo 'selected'; ?>>商品ID</option>
                    <option value="name" <?php if ($search_type === 'name') echo 'selected'; ?>>商品名</option>
                </select>
                
                <input type="text" name="search_keyword" value="<?php echo htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="検索キーワードを入力" class="search-input">
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