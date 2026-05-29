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
    <title>バックルームコンピューター - メニュー</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/menu.css">
</head>

<body>
    <div class="menu-container">
        <header>
            <div class="logout-area">
                <a href="logout.php" class="btn-logout">ログアウト</a>
            </div>
            <h1>バックルームコンピューター</h1>
        </header>

        <main class="grid-layout">
            <a href="stock_select.php" class="grid-item">
                <span class="label">在庫<br>確認・更新</span>
            </a>
            <a href="sales.php" class="grid-item">
                <span class="label">売上一覧</span>
            </a>
            <a href="Accounting_history.php" class="grid-item">
                <span class="label">会計履歴</span>
            </a>
            <a href="in_out_updt.php" class="grid-item">
                <span class="label">商品<br>登録・更新・削除</span>
            </a>
            <a href="staff_edit.php" class="grid-item staff-btn">
                <span class="label">社員<br>登録・更新・削除</span>
            </a>
        </main>
    </div>
</body>

</html>