<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社員情報削除画面 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/delete_staff.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <a href="staff_edit.php" class="btn-back">戻る</a>
            <h2 class="page-title">社員情報削除</h2>
            <div style="width: 82px;"></div> </div>
        
        <div class="search-section">
            <select id="search-type" class="search-select">
                <option value="emp_id">社員ID</option>
                <option value="emp_number">社員番号</option>
            </select>
            <input type="text" id="search-keyword" class="search-input" placeholder="社員ID・社員番号を入力してください">
            <button type="button" id="btn-search" class="btn-search">検索</button>
        </div>
        
        <div class="detail-section">
            <div id="no-data-message" class="no-data-msg">
                上の検索窓から社員を検索してください。ここに登録情報が表示されます。
            </div>
            
            <div id="detail-list" class="detail-wrapper" style="display: none;">
                <div class="detail-header">登録情報 (この画面では何も変更不可能)</div>
                
                <div class="detail-row">
                    <div class="detail-label">名前</div>
                    <div id="val-name" class="detail-value">-</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">カタカナ</div>
                    <div id="val-kana" class="detail-value">-</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">社員番号</div>
                    <div id="val-emp-number" class="detail-value">-</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">社員ID</div>
                    <div id="val-emp-id" class="detail-value">-</div>
                </div>
            </div>
        </div>
        
        <div class="bottom-section">
            <button type="button" id="btn-delete" class="btn-delete" disabled>削除する</button>
        </div>

    </div>

    <script src="js/delete_staff.js"></script>
</body>
</html>