<?php

// セッションの開始
session_start();

// ログインしていない場合はログイン画面へ強制リダイレクト
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// ブラウザのキャッシュを無効化
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'db.php'; 

// =========================================
// 【追加】Ajax：商品検索（選択肢を表示するため）
// =========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (($input['action'] ?? '') === 'search_products') {
        $type = $input['type'] ?? 'name';
        $keyword = $input['keyword'] ?? '';
        
        try {
            // ※注意：itemsテーブルのカラム名（item_name, barcodeなど）が違う場合はここを修正してください
            $sql = "SELECT item_id, item_name, category FROM items WHERE ";
            if ($type === 'id') {
                $sql .= "item_id = :keyword";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['keyword' => $keyword]);
            } elseif ($type === 'barcode') {
                $sql .= "barcode = :keyword";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['keyword' => $keyword]);
            } else {
                $sql .= "item_name LIKE :keyword";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['keyword' => '%' . $keyword . '%']);
            }
            
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'items' => $items]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
        }
        exit;
    }
}

// --- 初期表示（未検索・本日）用の全体データを取得 ---
$total_amount = 0;
$sales_count = 0;
$genres = [];

try {
    // 1. 本日の売上合計と会計回数を取得
    $stmt = $pdo->query("SELECT SUM(total_amount) as total, COUNT(*) as count FROM sales WHERE DATE(created_at) = CURDATE()");
    $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($sales_data) {
        $total_amount = $sales_data['total'] ?? 0;
        $sales_count = $sales_data['count'] ?? 0;
    }

    // 2. 本日の売れているカテゴリランキング (上位3つ)
    $sql_genre = "SELECT i.category, SUM(sd.quantity) as cnt 
                  FROM sale_details sd 
                  JOIN items i ON sd.item_id = i.item_id 
                  JOIN sales s ON sd.sale_id = s.sale_id
                  WHERE DATE(s.created_at) = CURDATE()
                  GROUP BY i.category 
                  ORDER BY cnt DESC 
                  LIMIT 3";
    $genre_stmt = $pdo->query($sql_genre);
    $genres = $genre_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // エラー時は必要に応じて処理
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>売上情報・商品検索 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/sales_detail.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <a href="sales.php" class="btn-back">戻る</a>
            
            <form class="search-container" id="search-form">
                <select class="search-select" id="search-type">
                    <option value="id">商品ID</option>
                    <option value="name">商品名</option>
                    <option value="barcode">バーコード番号</option>
                </select>
                <input type="text" id="search-input" placeholder="検索キーワードを入力" autocomplete="off">
                <button type="submit" id="btn-search">検索</button>
            </form>

            <div class="product-info-header" id="product-info-box" style="display: none;">
                <div class="info-text-group">
                    <span class="info-cell" id="info-id">商品ID: ------</span>
                    <span class="info-cell" id="info-name">商品名: ------</span>
                    <span class="info-cell" id="info-genre">ジャンル: ------</span>
                </div>
                <button type="button" class="btn-clear-search" id="btn-clear">✕ 解除</button>
            </div>
        </div>
        
        <div class="tab-group">
            <button class="tab-btn" data-period="過去1時間">過去1時間</button>
            <button class="tab-btn active" data-period="本日">本日</button>
            <button class="tab-btn" data-period="今週">今週</button>
            <button class="tab-btn" data-period="今月">今月</button>
            <button class="tab-btn" data-period="今年">今年</button>
        </div>

        <div id="view-product-list" style="display: none;">
            <h3>該当する商品を選択してください</h3>
            <div class="product-list-container" id="product-list-container">
                </div>
        </div>

        <div id="view-global-sales" style="display: flex; flex-direction: column; flex-grow: 1;">
            <div class="content-columns">
                <div class="column-left">
                    <div class="data-card large-card">
                        <h3 class="global-sales-title">本日の売り上げ</h3>
                        <div class="data-value class-global-sales-val"><?= number_format($total_amount) ?>円</div>
                    </div>
                </div>

                <div class="column-right">
                    <div class="data-card small-card">
                        <h3 class="global-count-title">本日の会計回数</h3>
                        <div class="data-value class-global-count-val"><?= number_format($sales_count) ?>回</div>
                    </div>

                    <div class="data-card small-card">
                        <h3>売れているジャンル</h3>
                        <div class="data-list" id="global-genre-list">
                            <?php if ($genres): ?>
                                <?php foreach ($genres as $index => $g): ?>
                                    <p><?= ($index+1) ?>. <?= htmlspecialchars($g['category']) ?></p>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>データなし</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="view-product-sales" style="display: none; flex-direction: column; flex-grow: 1;">
            <div class="product-stats-grid">
                <div class="data-card">
                    <h3 id="product-count-title">本日の売上個数</h3>
                    <div class="data-value" id="product-count-val">0個</div>
                </div>
                <div class="data-card">
                    <h3 id="product-amount-title">本日の売上金額</h3>
                    <div class="data-value" id="product-amount-val">0円</div>
                </div>
            </div>
        </div>
        
    </div>

    <script src="js/sales_detail.js"></script>
</body>
</html>