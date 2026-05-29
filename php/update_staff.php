<!DOCTYPE html>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社員情報更新画面 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/update_staff.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <a href="staff_edit.php" class="btn-back">戻る</a>
            <h2 class="page-title">社員情報更新</h2>
            <div style="width: 82px;"></div> </div>
        
        <div class="search-section">
            <select id="search-type" class="search-select">
                <option value="emp_id">社員ID</option>
                <option value="emp_number">社員番号</option>
            </select>
            <input type="text" id="search-keyword" class="search-input" placeholder="検索キーワードを入力してください">
            <button type="button" id="btn-search" class="btn-search">検索</button>
        </div>
        
        <div id="no-data-message" class="no-data-msg">
            変更したい社員を上の検索窓から検索してください。
        </div>

        <div id="update-form-area" class="update-form-area" style="display: none;">
            <form action="#" method="POST">
                
                <div class="form-wrapper">
                    
                    <div class="form-row">
                        <label>名前</label>
                        <input type="text" id="form-name" name="staff_name" class="readonly-input" readonly placeholder="名前（変更不可）">
                    </div>
                    
                    <div class="form-row">
                        <label>カタカナ</label>
                        <input type="text" id="form-kana" name="staff_kana" class="readonly-input" readonly placeholder="カタカナ（変更不可）">
                    </div>
                    
                    <div class="form-row">
                        <label>社員番号</label>
                        <input type="text" id="form-number" name="staff_number" placeholder="社員番号を入力">
                    </div>
                    
                    <div class="form-row">
                        <label>パスワード</label>
                        <input type="password" id="form-pass" name="staff_pass" placeholder="新しいパスワードを入力">
                    </div>

                </div>

                <div class="bottom-btn-wrapper">
                    <button type="submit" id="btn-submit" class="btn-submit-large">更新</button>
                </div>
                
            </form>
        </div>

    </div>

    <script src="js/update_staff.js"></script>
</body>
</html>