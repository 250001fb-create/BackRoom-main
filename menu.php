<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>バックルームコンピューター - メニュー</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="menu-container">
        <header>
            <div class="logout-area">
                <a href="index.php" class="btn-logout">ログアウト</a>
            </div>
            <h1>バックルームコンピューター</h1>
        </header>

        <main class="grid-layout">
            <a href="stock_select.php" class="grid-item">
                <span class="label">在庫</span>
            </a>
            <a href="sales.php" class="grid-item">
                <span class="label">売上</span>
            </a>
            <a href="Accounting_history.php" class="grid-item">
                <span class="label">会計履歴</span>
            </a>
            <a href="in_out_updt.php" class="grid-item">
                <span class="label">商品<br>登録・更新・削除</span>
            </a>
        </main>
    </div>
</body>
</html>