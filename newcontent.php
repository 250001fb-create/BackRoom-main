<?php
// データベース接続ファイルを読み込み
require_once 'db.php';

$message = '';
$error_message = '';

// フォームが送信されたときの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームからの入力を安全に取得
    $item_name = filter_input(INPUT_POST, 'product_name', FILTER_DEFAULT);
    $barcode = filter_input(INPUT_POST, 'barcode_number', FILTER_DEFAULT);
    $stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
    $tax_rate = filter_input(INPUT_POST, 'tax_rate', FILTER_VALIDATE_INT);
    $category = filter_input(INPUT_POST, 'genre', FILTER_DEFAULT);

    // 必須項目のバリデーション（チェック）
    if (empty($item_name) || $price === false || $stock_quantity === false) {
        $error_message = '商品名、単価、在庫数は必須項目です。正しく入力してください。';
    } else {
        try {
            // SQL文の準備（kanaは空文字、item_idは自動採番なので含めない）
            $sql = "INSERT INTO items (item_name, kana, barcode, price, tax_rate, category, stock_quantity) 
                    VALUES (:item_name, :kana, :barcode, :price, :tax_rate, :category, :stock_quantity)";
            
            $stmt = $pdo->prepare($sql);
            
            // プレースホルダに値をバインドして実行
            $stmt->execute([
                ':item_name' => $item_name,
                ':kana' => '', // フリガナ用の項目（必要であれば後からフォームを追加してください）
                ':barcode' => !empty($barcode) ? $barcode : null, // 空ならNULLを入れる
                ':price' => $price,
                ':tax_rate' => $tax_rate,
                ':category' => $category,
                ':stock_quantity' => $stock_quantity
            ]);

            $message = '商品を正常に新規登録しました！';
        } catch (PDOException $e) {
            $error_message = '登録エラー: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品登録画面 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/newcontent.css">
</head>
<body>
    <div class="main-container">
        <form action="" method="POST">
            
            <div class="top-section">
                <a href="in_out_updt.php" class="btn-back">戻る</a>
                <a href="update.php" class="btn-menu">更新メニューへ</a>
            </div>

            <?php if (!empty($message)): ?>
                <div style="background-color: #eef7f1; color: #146c36; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; text-align: center; border: 1px solid #146c36;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div style="background-color: #fde8e8; color: #e53e3e; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; text-align: center; border: 1px solid #e53e3e;">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <div class="form-grid">
                
                <div class="form-group">
                    <label>商品名 <span style="color:red;">*</span></label>
                    <input type="text" name="product_name" placeholder="商品名を入力" required>
                </div>
                
                <div class="form-group">
                    <label>バーコード番号</label>
                    <input type="text" name="barcode_number" placeholder="バーコード番号">
                </div>
                
                <div class="form-group" style="background-color: #ececec; opacity: 0.7;">
                    <label>商品ID</label>
                    <input type="text" placeholder="自動で採番されます" disabled style="background-color: #ececec; cursor: not-allowed;">
                </div>
                
                <div class="form-group">
                    <label>在庫数 <span style="color:red;">*</span></label>
                    <input type="number" name="stock_quantity" placeholder="0" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>単価 <span style="color:red;">*</span></label>
                    <input type="number" name="price" placeholder="0" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>税率</label>
                    <select name="tax_rate">
                        <option value="8">8% (軽減税率)</option>
                        <option value="10">10% (標準税率)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>ジャンル</label>
                    <select name="genre">
                        <option value="野菜">野菜</option>
                        <option value="肉・魚">肉・魚</option>
                        <option value="飲料">飲料</option>
                        <option value="食品">食品</option>
                        <option value="その他">その他</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>バーコードの有無</label>
                    <select name="barcode_status">
                        <option value="有り">有り</option>
                        <option value="無し">無し</option>
                    </select>
                </div>
                
            </div>

            <div class="bottom-btn-wrapper">
                <button type="submit" class="btn-submit-large">登録する</button>
            </div>
            
        </form>
    </div>
</body>
</html>