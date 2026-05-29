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

    <script>
        // テスト用のダミー社員データ
        const mockStaff = [
            { emp_id: "ID001", emp_number: "1001", name: "山田 太郎", kana: "ヤマダ タロウ", pass: "pass1234" },
            { emp_id: "ID002", emp_number: "1002", name: "鈴木 花子", kana: "スズキ ハナコ", pass: "hana5678" },
            { emp_id: "ID003", emp_number: "2001", name: "佐藤 次郎", kana: "サトウ ジロウ", pass: "jiro9999" }
        ];

        // HTML要素の取得
        const searchType = document.getElementById('search-type');
        const searchKeyword = document.getElementById('search-keyword');
        const btnSearch = document.getElementById('btn-search');
        const noDataMessage = document.getElementById('no-data-message');
        const updateFormArea = document.getElementById('update-form-area');
        
        // フォーム内の各パーツの取得
        const formName = document.getElementById('form-name');
        const formKana = document.getElementById('form-kana');
        const formNumber = document.getElementById('form-number');
        const formPass = document.getElementById('form-pass');
        const btnSubmit = document.getElementById('btn-submit');

        // 「検索」ボタンが押されたときの処理
        btnSearch.addEventListener('click', () => {
            const type = searchType.value;
            const keyword = searchKeyword.value.trim();
            
            if (!keyword) {
                alert("キーワードを入力してください。");
                return;
            }
            
            // 選択されたタイプ（社員ID/社員番号）に応じてデータを検索
            const foundStaff = mockStaff.find(staff => {
                if (type === 'emp_id') return staff.emp_id === keyword;
                if (type === 'emp_number') return staff.emp_number === keyword;
                return false;
            });
            
            if (foundStaff) {
                // 見つかった場合：各入力欄に現在のデータをセット
                formName.value = foundStaff.name;
                formKana.value = foundStaff.kana;
                formNumber.value = foundStaff.emp_number;
                formPass.value = foundStaff.pass;
                
                // 案内文を隠して、入力フォームを表示
                noDataMessage.style.display = 'none';
                updateFormArea.style.display = 'flex';
            } else {
                alert("該当する社員が見つかりませんでした。\n（※テスト用データ: 社員番号「1001」や 社員ID「ID001」でお試しください）");
                noDataMessage.style.display = 'flex';
                updateFormArea.style.display = 'none';
            }
        });

        // 「更新する」ボタンが押されたときの送信処理（テスト用）
        btnSubmit.addEventListener('click', (e) => {
            e.preventDefault(); 
            const updatedNumber = formNumber.value;
            alert(`社員情報を更新しました！\n新しい社員番号: ${updatedNumber}`);
            
            // 初期状態に戻す
            searchKeyword.value = "";
            noDataMessage.style.display = 'flex';
            updateFormArea.style.display = 'none';
        });
    </script>
</body>
</html>