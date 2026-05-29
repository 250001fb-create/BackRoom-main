<?php

// セッションの開始
session_start();

// ログインしていない（セッションに情報がない）場合は、ログイン画面へ強制リダイレクト
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// ブラウザのキャッシュを無効化するヘッダー（戻るボタン対策）
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


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
            
            // 更新完了後、検索条件を維持したまま、successフラグを付けてリダイレクト
            header("Location: stock_update.php?search-type=" . urlencode($s_type) . "&search-keyword=" . urlencode($s_keyword) . "&success=1");
            exit;
        } catch (PDOException $e) {
            exit('データ更新失敗: ' . $e->getMessage());
        }
    }
}

// 【検索処理】キーワードが入力されている場合に実行
if ($keyword !== '') {
    try {
        if ($search_type === 'id') {
            $stmt = $pdo->prepare('SELECT * FROM items WHERE item_id = :item_id');
            $stmt->execute(['item_id' => intval($keyword)]);
        } elseif ($search_type === 'barcode') {
            $stmt = $pdo->prepare('SELECT * FROM items WHERE barcode = :barcode');
            $stmt->execute(['barcode' => $keyword]);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM items WHERE item_name LIKE :item_name');
            $stmt->execute(['item_name' => '%' . $keyword . '%']);
        }
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $show_result = true;
    } catch (PDOException $e) {
        exit('データ取得失敗: ' . $e->getMessage());
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
        <div class="top-section">
            <a href="stock_select.php" class="btn-back">戻る</a>
            
            <form action="stock_update.php" method="GET" class="search-form">
                <select name="search-type" class="search-select">
                    <option value="name" <?php if ($search_type === 'name') echo 'selected'; ?>>商品名</option>
                    <option value="id" <?php if ($search_type === 'id') echo 'selected'; ?>>商品ID</option>
                    <option value="barcode" <?php if ($search_type === 'barcode') echo 'selected'; ?>>バーコード番号</option>
                </select>
                <input type="text" name="search-keyword" class="search-input" placeholder="検索キーワードを入力" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" required>
                <button type="submit" class="btn-search">検索</button>
            </form>
        </div>
        
        <div class="detail-section">
            <?php if (!$show_result): ?>
                <div class="no-data-msg">
                    上の検索窓から商品を検索してください。ここに在庫数の変更フォームが表示されます。
                </div>
            <?php else: ?>
            <div class="table-container">
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>商品ID</th>
                            <th>商品名</th>
                            <th>現在の在庫数</th>
                            <th>新しい在庫数</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">該当する商品がありません。</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <form action="stock_update.php" method="POST" id="form-<?php echo $item['item_id']; ?>">
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
            <?php endif; ?>
        </div>
    </div>

    <script src="js/stock_update.js"></script>
</body>
</html>