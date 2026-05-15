<?php
require_once 'db.php';

try {
    // 全商品をID順に取得
    $stmt = $pdo->query("SELECT * FROM items ORDER BY item_id ASC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DBエラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>在庫確認 - バックルーム</title>
    <link rel="stylesheet" href="style/stock_page.css">
</head>
<body>
    <div class="main-container">
        <div class="top-section">
            <a href="stock_select.php" class="btn-back">戻る</a>
            <div class="search-container">
                <input type="text" placeholder="商品IDの入力" class="search-input" id="search-id">
                <button class="btn-search" onclick="searchTable()">検索</button>
            </div>
        </div>
        <div class="table-container">
            <table class="stock-table" id="stock-table">
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th>商品ID</th>
                        <th>個数</th>
                        <th>ジャンル</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= htmlspecialchars($item['item_id']) ?></td>
                        <td><?= htmlspecialchars($item['stock_quantity']) ?></td>
                        <td><?= htmlspecialchars($item['genre'] ?? '未設定') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function searchTable() {
            const input = document.getElementById('search-id').value;
            const rows = document.querySelectorAll('#stock-table tbody tr');
            rows.forEach(row => {
                const idCell = row.cells[1].textContent;
                row.style.display = idCell.includes(input) ? '' : 'none';
            });
        }
    </script>
</body>
</html>