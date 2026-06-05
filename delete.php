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

// データベース接続ファイルを読み込み
require_once 'db.php';

// Ajax（Fetch API）からのリクエスト処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    // =========================================
    // 1. 商品検索処理（リストで返すように変更）
    // =========================================
    if ($action === 'search') {
        $type = $input['type'] ?? '';
        $keyword = $input['keyword'] ?? '';

        if ($keyword === '') {
            echo json_encode(['success' => false, 'message' => 'キーワードを入力してください。']);
            exit;
        }

        try {
            if ($type === 'id') {
                $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = :keyword");
                $stmt->execute([':keyword' => (int)$keyword]);
            } elseif ($type === 'barcode') {
                $stmt = $pdo->prepare("SELECT * FROM items WHERE barcode = :keyword");
                $stmt->execute([':keyword' => $keyword]);
            } else {
                // 商品名での部分一致検索
                $stmt = $pdo->prepare("SELECT * FROM items WHERE item_name LIKE :keyword");
                $stmt->execute([':keyword' => '%' . $keyword . '%']);
            }
            
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($products) {
                $result_data = [];
                foreach ($products as $product) {
                    $result_data[] = [
                        'id' => $product['item_id'],
                        'name' => $product['item_name'],
                        'barcode' => !empty($product['barcode']) ? $product['barcode'] : 'なし',
                        'price' => '¥' . number_format($product['price']),
                        'tax_rate' => $product['tax_rate'] . '%',
                        'category' => !empty($product['category']) ? $product['category'] : 'その他'
                    ];
                }
                echo json_encode(['success' => true, 'products' => $result_data]);
            } else {
                echo json_encode(['success' => false, 'message' => '該当する商品が見つかりませんでした。']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DBエラー: ' . $e->getMessage()]);
        }
        exit;
    }

    // =========================================
    // 2. 商品削除処理
    // =========================================
    if ($action === 'delete') {
        $id = $input['id'] ?? '';

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => '商品IDが正しくありません。']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => '削除に失敗しました: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品削除画面 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/delete.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <a href="in_out_updt.php" id="btn-top-back" class="btn-back">戻る</a>
            <h2 style="margin: 0 auto; color: #b33939; font-size: 20px;">商品の削除</h2>
        </div>
        
        <form id="search-form" class="search-container">
            <select id="search-type" class="search-select">
                <option value="name">商品名</option>
                <option value="id">商品ID</option>
                <option value="barcode">バーコード番号</option>
            </select>
            <input type="text" id="search-keyword" class="search-input" placeholder="検索キーワードを入力してください" autocomplete="off">
            <button type="submit" class="btn-search">検索</button>
        </form>

        <div id="table-container" class="table-container" style="display: none;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>商品ID</th>
                        <th>商品名</th>
                        <th>ジャンル</th>
                        <th>単価</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="results-tbody">
                    </tbody>
            </table>
        </div>
        
        <div id="detail-section-wrapper" class="detail-section">
            <div id="no-data-message" class="no-data-msg">
                上の検索窓から商品を検索してください。ここに商品の詳細が表示されます。
            </div>
            
            <div id="detail-grid" class="detail-grid" style="display: none;">
                <div class="grid-header">商品名</div>
                <div class="grid-value" id="val-name"></div>
                
                <div class="grid-header">バーコード</div>
                <div class="grid-value" id="val-barcode"></div>
                
                <div class="grid-header">商品ID</div>
                <div class="grid-value" id="val-id"></div>
                
                <div class="grid-header">単価</div>
                <div class="grid-value" id="val-price"></div>
                
                <div class="grid-header">税率</div>
                <div class="grid-value" id="val-taxin"></div>
                
                <div class="grid-header">ジャンル</div>
                <div class="grid-value" id="val-genre"></div>
            </div>
        </div>

        <div class="bottom-section">
            <button type="button" id="btn-delete" class="btn-delete" disabled>削除する</button>
        </div>
        
    </div>

    <script src="js/delete.js"></script>
</body>
</html>