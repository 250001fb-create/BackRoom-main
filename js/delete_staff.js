// テスト用のダミー社員データ
const mockStaff = [
    { emp_id: "ID001", emp_number: "1001", name: "山田 太郎", kana: "ヤマダ タロウ" },
    { emp_id: "ID002", emp_number: "1002", name: "鈴木 花子", kana: "スズキ ハナコ" },
    { emp_id: "ID003", emp_number: "2001", name: "佐藤 次郎", kana: "サトウ ジロウ" }
];

// HTML要素の取得
const searchType = document.getElementById('search-type');
const searchKeyword = document.getElementById('search-keyword');
const btnSearch = document.getElementById('btn-search');

const noDataMessage = document.getElementById('no-data-message');
const detailList = document.getElementById('detail-list');

const valName = document.getElementById('val-name');
const valKana = document.getElementById('val-kana');
const valEmpNumber = document.getElementById('val-emp-number');
const valEmpId = document.getElementById('val-emp-id');

const btnDelete = document.getElementById('btn-delete');

// 「検索」ボタンが押されたときの処理
btnSearch.addEventListener('click', () => {
    const type = searchType.value;
    const keyword = searchKeyword.value.trim();
    
    if (!keyword) {
        alert("キーワードを入力してください。");
        return;
    }
    
    // 選択されたプルダウンのタイプに応じてデータを検索
    const foundStaff = mockStaff.find(staff => {
        if (type === 'emp_id') return staff.emp_id === keyword;
        if (type === 'emp_number') return staff.emp_number === keyword;
        return false;
    });
    
    if (foundStaff) {
        // 見つかったら値をハメ込んで表示する
        valName.textContent = foundStaff.name;
        valKana.textContent = foundStaff.kana;
        valEmpNumber.textContent = foundStaff.emp_number;
        valEmpId.textContent = foundStaff.emp_id;
        
        noDataMessage.style.display = 'none';
        detailList.style.display = 'flex';
        btnDelete.disabled = false; // 削除ボタンを押せるようにする
    } else {
        alert("該当する社員が見つかりませんでした。\n（※テスト用データ: 社員番号「1001」や 社員ID「ID001」でお試しください）");
        
        // 見つからなかったらリセット
        noDataMessage.style.display = 'block';
        detailList.style.display = 'none';
        btnDelete.disabled = true;
    }
});

// 「削除する」ボタンが押されたときの処理（確認ダイアログ）
btnDelete.addEventListener('click', () => {
    const staffName = valName.textContent;
    
    // ブラウザの確認ダイアログ（ポップアップ）を出す
    const isConfirmed = confirm(`本当に社員「${staffName}」の情報を削除してもよろしいですか？\n※この操作は取り消せません。`);
    
    if (isConfirmed) {
        alert(`社員「${staffName}」の情報を削除しました。`);
        
        // 削除後、画面を初期状態に戻す
        searchKeyword.value = "";
        noDataMessage.style.display = 'block';
        detailList.style.display = 'none';
        btnDelete.disabled = true;
    }
});