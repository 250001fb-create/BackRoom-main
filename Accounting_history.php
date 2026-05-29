<?php
require_once 'db.php';

try {
    // 【修正】決済方法(payment_method)も一緒に取得するように変更
    $stmt = $pdo->query("SELECT sale_id, created_at, total_amount, payment_method FROM sales ORDER BY created_at DESC");
    $histories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DBエラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>会計履歴 - バックルーム</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/Accounting_history.css">
</head>
<body>
    <div class="main-container">
        <div class="top-section">
            <a href="menu.php" class="btn-back">戻る</a>
            <div class="search-container">
                <input type="text" placeholder="会計IDを入力" class="search-input" id="history-search">
                <button class="btn-search">検索</button>
            </div>
        </div>
        <div class="table-container">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>会計ID</th>
                        <th>時間</th>
                        <th>合計金額</th>
                        <th>支払方法</th> <th>詳細</th> </tr>
                </thead>
                <tbody>
                    <?php foreach ($histories as $h): ?>
                    <tr>
                        <td><?= sprintf('%05d', $h['sale_id']) ?></td>
                        <td><?= $h['created_at'] ?></td>
                        <td>¥<?= number_format($h['total_amount']) ?></td>
                        <td><?= htmlspecialchars($h['payment_method'] ?? '不明') ?></td> <td>
                            <button class="btn-detail" 
                                    data-id="<?= $h['sale_id'] ?>" 
                                    data-time="<?= $h['created_at'] ?>"
                                    data-method="<?= htmlspecialchars($h['payment_method'] ?? '不明') ?>"
                                    data-total="¥<?= number_format($h['total_amount']) ?>">
                                詳細
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <h2 style="margin-top:0; color:#333; font-size: 20px; border-bottom: 2px solid #146c36; padding-bottom: 8px;">注文詳細</h2>
            
            <div class="modal-meta">
                <div><strong>会計ID:</strong> <span id="modalSaleId"></span></div>
                <div><strong>日時:</strong> <span id="modalTime"></span></div>
                <div><strong>支払方法:</strong> <span id="modalMethod"></span></div>
                <div><strong>合計金額:</strong> <span id="modalTotal" style="font-weight:bold; color:#146c36;"></span></div>
            </div>
            
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th>単価</th>
                        <th>数量</th>
                        <th>小計</th>
                    </tr>
                </thead>
                <tbody id="modalTableBody">
                    </tbody>
            </table>
        </div>
    </div>

    <script src="js/Accounting_history.js"></script>
</body>
</html>