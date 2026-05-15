<?php
require_once 'db.php';

// 期間の判定（デフォルトは本日）
$range = $_GET['range'] ?? 'today';

switch ($range) {
    case 'hour':
        $where = "created_at >= NOW() - INTERVAL 1 HOUR";
        break;
    case 'week':
        $where = "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $where = "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        break;
    case 'year':
        $where = "created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        break;
    case 'today':
    default:
        $where = "DATE(created_at) = CURDATE()";
        break;
}

try {
    // 売上合計と回数を取得
    $stmt = $pdo->query("SELECT SUM(total_amount) as total, COUNT(*) as count FROM sales WHERE $where");
    $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_amount = $sales_data['total'] ?? 0;
    $sales_count = $sales_data['count'] ?? 0;

    // 売れているジャンルランキング (上位3つ)
    $sql_genre = "SELECT i.genre, SUM(sd.quantity) as cnt 
              FROM sale_details sd 
              JOIN items i ON sd.item_id = i.item_id 
              JOIN sales s ON sd.sale_id = s.sale_id
              WHERE $where
              GROUP BY i.genre 
              ORDER BY cnt DESC 
              LIMIT 3";

    try {
        $genre_stmt = $pdo->prepare($sql_genre);
        $genre_stmt->execute();
        $genres = $genre_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // ここでエラーが出ても画面全体が止まらないように空配列にする
        $genres = [];
    }

} catch (PDOException $e) {
    die("DBエラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>売上情報 - バックルーム</title>
    <link rel="stylesheet" href="style/sales.css">
    <style>
        /* 選択中のタブを強調するスタイル */
        .tab-btn.active { background-color: #146c36; color: white; }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="top-section">
            <a href="menu.php" class="btn-back">戻る</a>
        </div>
        
        <div class="tab-group">
            <a href="?range=hour" class="tab-btn <?= $range=='hour'?'active':'' ?>">過去1時間</a>
            <a href="?range=today" class="tab-btn <?= $range=='today'?'active':'' ?>">本日</a>
            <a href="?range=week" class="tab-btn <?= $range=='week'?'active':'' ?>">今週</a>
            <a href="?range=month" class="tab-btn <?= $range=='month'?'active':'' ?>">今月</a>
        </div>

        <div class="dashboard-grid">
            <div class="data-card">
                <h3>選択期間の売り上げ</h3>
                <div class="data-value">¥<?= number_format($total_amount) ?></div>
            </div>

            <div class="data-card">
                <h3>対する会計回数</h3>
                <div class="data-value"><?= $sales_count ?>回</div>
            </div>

            <div class="data-card">
                <h3>売れているジャンル</h3>
                <div class="data-list">
                    <?php if ($genres): ?>
                        <?php foreach ($genres as $index => $g): ?>
                            <p><?= ($index+1) ?>. <?= htmlspecialchars($g['genre']) ?> (<?= $g['cnt'] ?>点)</p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>データなし</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="weather-and-btn">
                <div class="weather-card">
                    <h3>現在の天気</h3>
                    <div id="weather-info" class="data-value weather-value">取得中...</div>
                </div>
                <a href="Accounting_history.php" class="btn-detail">履歴一覧へ</a>
            </div>
        </div>
    </div>

    <script>
        // 天気情報の取得（既存のスクリプトを継続）
        fetch('https://www.jma.go.jp/bosai/forecast/data/forecast/140000.json')
            .then(response => response.json())
            .then(data => {
                const weatherText = data[0].timeSeries[0].areas[0].weathers[0];
                document.getElementById('weather-info').textContent = weatherText.replace(/　/g, ' ');
            })
            .catch(() => { document.getElementById('weather-info').textContent = 'エラー'; });
    </script>
</body>
</html>