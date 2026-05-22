<?php
require_once 'db.php';

// GETパラメータ（検索条件）の取得
$search_type = isset($_GET['search-type']) ? $_GET['search-type'] : 'id';
$keyword = isset($_GET['search-keyword']) ? trim($_GET['search-keyword']) : '';
$items = [];
$show_result = false;

// 【更新処理】POSTリクエスト（更新ボタンが押されたとき）の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $new_stock = isset($_POST['new_stock']) ? intval($_POST['new_stock']) : 0;
    
    // 更新後に元の検索画面を維持するためのパラメータ
    $s_type = isset($_POST['search-type']) ? $_POST['search-type'] : 'id';
    $s_keyword = isset($_POST['search-keyword']) ? $_POST['search-keyword'] : '';

    if ($item_id > 0) {
        try {
            // データベースの在庫数を更新
            $stmt = $pdo->prepare('UPDATE items SET stock_quantity = :stock_quantity WHERE item_id = :item_id');
            $stmt->execute([
                'stock_quantity' => $new_stock,
                'item_id' => $item_id
            ]);
            
            // 更新完了後、検索状態を維持したURLにリダイレクト（二重送信防止）
            header("Location: stock_update.php?search-type=" . urlencode($s_type) . "&search-keyword=" . urlencode($s_keyword) . "&success=1");
            exit;
        } catch (PDOException $e) {
            exit('更新失敗: ' . $e->getMessage());
        }
    }
}

// 【検索処理】キーワードが指定されている場合、データを取得
if ($keyword !== '') {
    $show_result = true;
    try {
        if ($search_type === 'id') {
            // 商品IDで検索（完全一致）
            $stmt = $pdo->prepare('SELECT * FROM items WHERE item_id = :id');
            $stmt->execute(['id' => $keyword]);
        } else {
            // 商品名で検索（部分一致）
            $stmt = $pdo->prepare('SELECT * FROM items WHERE item_name LIKE :name');
            $stmt->execute(['name' => '%' . $keyword . '%']);
        }
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        exit('検索失敗: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫数変更 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/stock_update.css">
</head>
<body>
    <div class="main-container">
        <a href="stock_select.php" class="btn-back">戻る</a>

        <div class="search-section">
            <form action="stock_update.php" method="GET" class="search-container">
                <select name="search-type" id="search-type" class="search-select">
                    <option value="id" <?php echo $search_type === 'id' ? 'selected' : ''; ?>>商品ID</option>
                    <option value="name" <?php echo $search_type === 'name' ? 'selected' : ''; ?>>商品名</option>
                </select>
                <input type="text" name="search-keyword" id="search-keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="キーワードを入力" class="search-input" required>
                <button type="submit" id="btn-search" class="btn-search">検索</button>
            </form>
        </div>

        <div id="result-section" class="result-section <?php echo $show_result ? '' : 'hidden'; ?>">
            <div class="table-container">
                <table class="result-table">
                    <thead>
                        <tr>
                            <th>商品ID</th>
                            <th>商品名</th>
                            <th>現在の在庫</th>
                            <th>変更後の在庫</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="result-body">
                        <?php if (empty($items) && $show_result): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">該当する商品が見つかりませんでした。</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <form id="form-<?php echo $item['item_id']; ?>" action="stock_update.php" method="POST">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                    <input type="hidden" name="search-type" value="<?php echo htmlspecialchars($search_type, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="search-keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>">
                                </form>

                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($item['stock_quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <input type="number" name="new_stock" form="form-<?php echo $item['item_id']; ?>" class="inline-input" value="<?php echo htmlspecialchars($item['stock_quantity'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </td>
                                    <td>
                                        <button type="submit" form="form-<?php echo $item['item_id']; ?>" class="btn-inline-update">更新</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
        <script>
            alert("在庫数を更新しました");
        </script>
    <?php endif; ?>
</body>
</html>