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
    <title>店員登録・更新・削除 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/in_out_updt.css">
</head>
<body>
    <div class="main-container">
        <a href="menu.php" class="btn-back">戻る</a>
        
        <main class="button-group">
            <a href="new_staff.php" class="action-btn">登録</a>
            <a href="update_staff.php" class="action-btn">更新</a>
            <a href="delete_staff.php" class="action-btn">削除</a>
        </main>
    </div>
</body>
</html>