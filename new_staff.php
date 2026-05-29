<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社員登録 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/new_staff.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <a href="staff_edit.php" class="btn-back">戻る</a>
            <h2 class="page-title">社員登録</h2>
            <div style="width: 82px;"></div>
        </div>

        <form action="#" method="POST">
            <div class="form-wrapper">
                <div class="form-group">
                    <label for="staff_name">名前</label>
                    <input type="text" id="staff_name" name="staff_name" placeholder="例: 山田 太郎" required>
                </div>
                <div class="form-group">
                    <label for="staff_kana">読み</label>
                    <input type="text" id="staff_kana" name="staff_kana" placeholder="例: ヤマダ タロウ" required>
                </div>
                <div class="form-group">
                    <label for="staff_id">社員番号</label>
                    <input type="text" id="staff_id" name="staff_id" placeholder="例: 1001" required>
                </div>
                <div class="form-group">
                    <label for="staff_pass">パスワード</label>
                    <input type="password" id="staff_pass" name="staff_pass" placeholder="パスワードを入力" required>
                </div>
            </div>

            <div class="bottom-btn-wrapper">
                <button type="submit" class="btn-submit-large">登録</button>
            </div>
        </form>

    </div>
</body>
</html>