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

// 期間の判定（デフォルトは本日）
$range = $_GET['range'] ?? 'today';
$title_prefix = '本日'; // 画面に表示するタイトル用の変数

switch ($range) {
    case 'hour':
        $where = "created_at >= NOW() - INTERVAL 1 HOUR";
        $title_prefix = '過去1時間';
        break;
    case 'week':
        $where = "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $title_prefix = '今週';
        break;
    case 'month':
        $where = "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $title_prefix = '今月';
        break;
    case 'year':
        $where = "created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        $title_prefix = '今年';
        break;
    case 'today':
    default:
        $where = "DATE(created_at) = CURDATE()";
        $title_prefix = '本日';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>売上情報 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/sales.css">
    <style>
        /* Aタグをボタン風にするための微調整（念のため） */
        .tab-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
        }
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
            <a href="?range=year" class="tab-btn <?= $range=='year'?'active':'' ?>">今年</a>
        </div>

        <div class="dashboard-grid">
            <div class="data-card">
                <h3 id="sales-title"><?= htmlspecialchars($title_prefix) ?>の売り上げ</h3>
                <div class="data-value" id="sales-value"><?= number_format($total_amount) ?>円</div>
            </div>

            <div class="data-card">
                <h3 id="count-title"><?= htmlspecialchars($title_prefix) ?>の会計回数</h3>
                <div class="data-value" id="count-value"><?= number_format($sales_count) ?>回</div>
            </div>

            <div class="data-card">
                <h3>売れているジャンル</h3>
                <div class="data-list" id="genre-list">
                    <?php if ($genres): ?>
                        <?php foreach ($genres as $index => $g): ?>
                            <p><?= ($index+1) ?>. <?= htmlspecialchars($g['genre']) ?></p>
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
                <a href="sales_detail.php" class="btn-detail">詳細へ</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            /* ----------------------------------------------------
               天気予報の取得処理（気象庁オープンデータ）
               ※ダミーのタブ切り替えはPHP化したため削除しました
            ---------------------------------------------------- */
            fetch('https://www.jma.go.jp/bosai/forecast/data/forecast/140000.json')
                .then(response => response.json())
                .then(data => {
                    const weatherText = data[0].timeSeries[0].areas[0].weathers[0];
                    document.getElementById('weather-info').textContent = weatherText.replace(/　/g, ' ');
                })
                .catch(error => {
                    document.getElementById('weather-info').textContent = '情報取得エラー';
                    console.error('天気情報の取得に失敗:', error);
                });
        });
    </script>
</body>
</html>