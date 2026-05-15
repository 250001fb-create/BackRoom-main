<?php
require_once 'db.php';

try {
    // 最新の会計から順に表示
    $stmt = $pdo->query("SELECT sale_id, created_at, total_amount FROM sales ORDER BY created_at DESC");
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
    <link rel="stylesheet" href="style/accounting_history.css">
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($histories as $h): ?>
                    <tr>
                        <td><?= sprintf('%05d', $h['sale_id']) ?></td>
                        <td><?= $h['created_at'] ?></td>
                        <td>¥<?= number_format($h['total_amount']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>