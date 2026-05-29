// テスト用のダミー社員データ
const mockStaff = [
    { emp_id: "ID001", emp_number: "1001", name: "山田 太郎", kana: "ヤマダ タロウ", pass: "12345" },
    { emp_id: "ID002", emp_number: "1002", name: "鈴木 花子", kana: "スズキ ハナコ", pass: "67890" },
    { emp_id: "ID003", emp_number: "2001", name: "佐藤 次郎", kana: "サトウ ジロウ", pass: "abcde" }
];

// HTML要素の取得
const searchType = document.getElementById('search-type');
const searchKeyword = document.getElementById('search-keyword');
const btnSearch = document.getElementById('btn-search');

const noDataMessage = document.getElementById('no-data-message');
const updateFormArea = document.getElementById('update-form-area');

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