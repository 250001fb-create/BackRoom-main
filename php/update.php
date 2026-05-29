<?php
require_once 'db.php';

$product = null;
$all_items = [];
$error_message = '';
$success_message = '';

// =========================================
// 1. 「更新する」ボタンが押された時の処理 (POST)
// =========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $barcode = $_POST['barcode'];
    $price = (int)$_POST['price'];
    $tax_rate = (int)$_POST['tax_rate'];
    $category = $_POST['category'];
    $stock_quantity = (int)$_POST['stock_quantity'];

    try {
        $sql = "UPDATE items SET 
                    item_name = :item_name, 
                    barcode = :barcode, 
                    price = :price, 
                    tax_rate = :tax_rate, 
                    category = :category, 
                    stock_quantity = :stock_quantity 
                WHERE item_id = :item_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':item_name' => $item_name,
            ':barcode' => $barcode,
            ':price' => $price,
            ':tax_rate' => $tax_rate,
            ':category' => $category,
            ':stock_quantity' => $stock_quantity,
            ':item_id' => $item_id
        ]);
        
        $success_message = "「" . htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8') . "」の情報を更新しました！";
    } catch (PDOException $e) {
        $error_message = "更新エラーが発生しました: " . $e->getMessage();
    }
}

// =========================================
// 2. 特定の商品が「選択」された時の処理 (GETでitem_idが渡される)
// =========================================
if (isset($_GET['item_id']) && $_GET['item_id'] !== '') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = :item_id");
        $stmt->execute([':item_id' => $_GET['item_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error_message = "指定された商品が見つかりません。";
        }
    } catch (PDOException $e) {
        $error_message = "データ取得エラー: " . $e->getMessage();
    }
}

// =========================================
// 3. 商品が選択されていない場合、全商品の一覧を取得
// =========================================
if (!$product) {
    try {
        // 全商品をID順で取得
        $stmt = $pdo->query("SELECT * FROM items ORDER BY item_id ASC");
        $all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = "一覧取得エラー: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品情報更新画面 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/update.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <?php if ($product): ?>
                <a href="update.php" class="btn-back">一覧に戻る</a>
                <h2 style="margin: 0 auto; color: #146c36; font-size: 20px;">商品情報の編集</h2>
            <?php else: ?>
                <a href="in_out_updt.php" class="btn-back">戻る</a>
                <h2 style="margin: 0 auto; color: #146c36; font-size: 20px;">商品一覧から選択</h2>
            <?php endif; ?>
        </div>
        
        <?php if ($success_message): ?>
            <div style="background-color: #eef7f1; color: #146c36; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; text-align: center; border: 1px solid #146c36;">
                <?= $success_message ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div style="background-color: #fde8e8; color: #e53e3e; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; text-align: center; border: 1px solid #e53e3e;">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if ($product): ?>
        <div id="update-form-area" class="update-form-area" style="display: flex; flex-direction: column; flex-grow: 1;">
            <form action="update.php" method="POST" style="display: flex; flex-direction: column; height: 100%;">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="item_id" value="<?= htmlspecialchars($product['item_id'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label>商品名</label>
                        <input type="text" name="item_name" value="<?= htmlspecialchars($product['item_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>バーコード番号</label>
                        <input type="text" name="barcode" value="<?= htmlspecialchars($product['barcode'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>商品ID (変更不可)</label>
                        <input type="text" value="<?= sprintf('%05d', $product['item_id']) ?>" disabled style="background-color: #ececec; cursor: not-allowed;">
                    </div>
                    
                    <div class="form-group">
                        <label>在庫数</label>
                        <input type="number" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>単価</label>
                        <input type="number" name="price" value="<?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>税率</label>
                        <select name="tax_rate">
                            <option value="8" <?= $product['tax_rate'] == 8 ? 'selected' : '' ?>>8% (軽減税率)</option>
                            <option value="10" <?= $product['tax_rate'] == 10 ? 'selected' : '' ?>>10% (標準税率)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>ジャンル</label>
                        <select name="category">
                            <option value="野菜" <?= $product['category'] === '野菜' ? 'selected' : '' ?>>野菜</option>
                            <option value="肉・魚" <?= $product['category'] === '肉・魚' ? 'selected' : '' ?>>肉・魚</option>
                            <option value="飲料" <?= $product['category'] === '飲料' ? 'selected' : '' ?>>飲料</option>
                            <option value="食品" <?= $product['category'] === '食品' ? 'selected' : '' ?>>食品</option>
                            <option value="その他" <?= $product['category'] === 'その他' ? 'selected' : '' ?>>その他</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: auto; display: flex; justify-content: center; padding-bottom: 20px;">
                    <button type="submit" class="btn-submit-large" style="width: 100%; height: 60px; font-size: 18px; background-color: #146c36; color: #fff; border: none; border-radius: 8px; cursor: pointer;">情報を更新する</button>
                </div>
            </form>
        </div>

        <?php else: ?>
        <div class="table-container">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>商品ID</th>
                        <th>商品名</th>
                        <th>ジャンル</th>
                        <th>単価</th>
                        <th>在庫数</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_items)): ?>
                        <tr><td colspan="6">登録されている商品がありません。</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_items as $item): ?>
                        <tr>
                            <td><?= sprintf('%05d', $item['item_id']) ?></td>
                            <td style="text-align: left; font-weight: bold;"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($item['category'] ?? 'その他', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>¥<?= number_format($item['price']) ?></td>
                            <td><?= htmlspecialchars($item['stock_quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="update.php?item_id=<?= $item['item_id'] ?>" class="btn-select">選択</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>