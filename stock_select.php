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
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫管理 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/stock.css">
</head>
<body>
    <div class="stock-container">
        <a href="menu.php" class="btn-back">戻る</a>
        
        <main class="button-group">
            <a href="stock.php" class="action-btn">在庫一覧表示</a>
            <a href="stock_update.php" class="action-btn">在庫数変更</a>
        </main>
    </div>
</body>
</html>