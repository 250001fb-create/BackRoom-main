// HTML要素の取得
const searchForm = document.getElementById('search-form');
const searchType = document.getElementById('search-type');
const searchKeyword = document.getElementById('search-keyword');

const noDataMessage = document.getElementById('no-data-message');
const updateFormArea = document.getElementById('update-form-area');
const updateForm = document.getElementById('update-form');

const formName = document.getElementById('form-name');
const formKana = document.getElementById('form-kana');
const formNumber = document.getElementById('form-number');
const formPass = document.getElementById('form-pass');

// 状態保持用
let currentStaffId = null;

// ==========================================
// 1. 検索処理
// ==========================================
searchForm.addEventListener('submit', (e) => {
    e.preventDefault(); 
    
    const type = searchType.value;
    const keyword = searchKeyword.value.trim();
    
    if (!keyword) {
        alert("キーワードを入力してください。");
        return;
    }
    
    fetch('update_staff.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'search', type: type, keyword: keyword })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const staff = data.staff;
            currentStaffId = staff.staff_id;

            // 取得したデータを入れる
            formName.value = staff.staff_name;
            formKana.value = staff.kana;
            formNumber.value = staff.staff_number;
            formPass.value = ""; // パスワードは空にする（変更時のみ入力）
            
            // 案内文を隠して、入力フォームを表示
            noDataMessage.style.display = 'none';
            updateFormArea.style.display = 'flex';
        } else {
            alert(data.message);
            resetScreen();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('検索中にエラーが発生しました。');
    });
});

// ==========================================
// 2. 更新処理
// ==========================================
updateForm.addEventListener('submit', (e) => {
    e.preventDefault(); 

    if (!currentStaffId) return;

    const newName = formName.value.trim();
    const newKana = formKana.value.trim();
    const newNumber = formNumber.value.trim();
    const newPass = formPass.value;

    const isConfirmed = confirm('表示されている内容で社員情報を更新しますか？');
    if (!isConfirmed) return;

    fetch('update_staff.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            action: 'update', 
            target_id: currentStaffId, 
            staff_name: newName,
            kana: newKana,
            staff_number: newNumber,
            staff_pass: newPass 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('社員情報を正常に更新しました！');
            searchKeyword.value = "";
            resetScreen();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('更新処理中にエラーが発生しました。');
    });
});

// 画面初期化用関数
function resetScreen() {
    currentStaffId = null;
    noDataMessage.style.display = 'flex';
    updateFormArea.style.display = 'none';
    
    formName.value = "";
    formKana.value = "";
    formNumber.value = "";
    formPass.value = "";
}