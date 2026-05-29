<?php
// データベース接続ファイルを読み込み
require_once 'db.php';

// Ajax（Fetch API）からのリクエスト（検索または削除）を処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    // 送信されたJSONデータを取得
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    // =========================================
    // 1. 商品検索処理
    // =========================================
    if ($action === 'search') {
        $type = $input['type'] ?? '';
        $keyword = $input['keyword'] ?? '';

        if (empty($keyword)) {
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
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                // フロントエンドに返す商品データを成形
                echo json_encode([
                    'success' => true,
                    'product' => [
                        'id' => $product['item_id'],
                        'name' => $product['item_name'],
                        'barcode' => !empty($product['barcode']) ? $product['barcode'] : 'なし',
                        'price' => '¥' . number_format($product['price']),
                        'tax_rate' => $product['tax_rate'] . '%',
                        'category' => !empty($product['category']) ? $product['category'] : 'その他'
                    ]
                ]);
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
            // データベースから指定IDの商品を削除
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
            <a href="in_out_updt.php" class="btn-back">戻る</a>
        </div>
        
        <div class="search-section">
            <select id="search-type" class="search-select">
                <option value="name">商品名</option>
                <option value="id">商品ID</option>
                <option value="barcode">バーコード番号</option>
            </select>
            <input type="text" id="search-keyword" class="search-input" placeholder="検索キーワードを入力してください">
            <button type="button" id="btn-search" class="btn-search">検索</button>
        </div>
        
        <div class="detail-section">
            <div id="no-data-message" class="no-data-msg">
                上の検索窓から商品を検索してください。ここに商品の詳細が表示されます。
            </div>
            
            <div id="detail-grid" class="detail-grid" style="display: none;">
                <div class="grid-header">商品名</div>
                <div class="grid-value" id="val-name"></div>
                
                <div class="grid-header">バーコード番号</div>
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

    <script>
        // HTML要素の取得
        const searchType = document.getElementById('search-type');
        const searchKeyword = document.getElementById('search-keyword');
        const btnSearch = document.getElementById('btn-search');
        
        const noDataMessage = document.getElementById('no-data-message');
        const detailGrid = document.getElementById('detail-grid');
        
        const valName = document.getElementById('val-name');
        const valBarcode = document.getElementById('val-barcode');
        const valId = document.getElementById('val-id');
        const valPrice = document.getElementById('val-price');
        const valTaxin = document.getElementById('val-taxin');
        const valGenre = document.getElementById('val-genre');
        
        const btnDelete = document.getElementById('btn-delete');

        // 現在検索して表示している商品のIDを記憶しておく変数
        let currentProductId = null;

        // 「検索」ボタンが押されたときの処理
        btnSearch.addEventListener('click', () => {
            const type = searchType.value;
            const keyword = searchKeyword.value.trim();

            if (!keyword) {
                alert('検索キーワードを入力してください。');
                return;
            }

            // PHP側に非同期で検索リクエストを送信
            fetch('delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'search', type: type, keyword: keyword })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = data.product;
                    currentProductId = product.id; // 削除時に使用するためにIDをキープ

                    // 画面の各項目にデータベースから取得した値をセット
                    valName.textContent = product.name;
                    valBarcode.textContent = product.barcode;
                    valId.textContent = String(product.id).padStart(5, '0'); // 5桁に揃えて表示
                    valPrice.textContent = product.price;
                    valTaxin.textContent = product.tax_rate;
                    valGenre.textContent = product.category;
                    
                    // 表示を切り替えて削除ボタンを押せるようにする
                    noDataMessage.style.display = 'none';
                    detailGrid.style.display = 'grid';
                    btnDelete.disabled = false;
                } else {
                    alert(data.message);
                    resetScreen();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('検索中にエラーが発生しました。');
            });
        });

        // 「削除する」ボタンが押されたときの処理
        btnDelete.addEventListener('click', () => {
            if (!currentProductId) return;

            const productName = valName.textContent;
            
            // 誤操作防止の確認アラート
            const isConfirmed = confirm(`本当に「${productName}」を削除してもよろしいですか？\n※この操作は取り消せません。`);
            
            if (isConfirmed) {
                // PHP側に非同期で削除リクエストを送信
                fetch('delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', id: currentProductId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`「${productName}」をデータベースから完全に削除しました。`);
                        searchKeyword.value = "";
                        resetScreen(); // 画面を初期状態に戻す
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('削除処理中にエラーが発生しました。');
                });
            }
        });

        // 画面の表示をリセット（データなし状態に戻す）関数
        function resetScreen() {
            currentProductId = null;
            noDataMessage.style.display = 'block';
            detailGrid.style.display = 'none';
            btnDelete.disabled = true;
            
            valName.textContent = "";
            valBarcode.textContent = "";
            valId.textContent = "";
            valPrice.textContent = "";
            valTaxin.textContent = "";
            valGenre.textContent = "";
        }
    </script>
</body>
</html>