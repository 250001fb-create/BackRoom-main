// HTML要素の取得
const searchType = document.getElementById('search-type');
const searchKeyword = document.getElementById('search-keyword');
const btnSearch = document.getElementById('btn-search');

const noDataMessage = document.getElementById('no-data-message');
const detailGrid = document.getElementById('detail-grid');

const valName = document.getElementById('val-name');
const valBarcode = document.getElementById('val-barcode');
const valId = document.getElementById('val-id');
const valPrice = document.getElementById('val-price');
const valTaxin = document.getElementById('val-taxin');
const valGenre = document.getElementById('val-genre');

const btnDelete = document.getElementById('btn-delete');

// 現在検索して表示している商品のIDを記憶しておく変数
let currentProductId = null;

// 「検索」ボタンが押されたときの処理
btnSearch.addEventListener('click', () => {
    const type = searchType.value;
    const keyword = searchKeyword.value.trim();

    if (!keyword) {
        alert('検索キーワードを入力してください。');
        return;
    }

    // PHP側に非同期で検索リクエストを送信
    fetch('delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'search', type: type, keyword: keyword })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const product = data.product;
            currentProductId = product.id; // 削除時に使用するためにIDをキープ

            // 画面の各項目にデータベースから取得した値をセット
            valName.textContent = product.name;
            valBarcode.textContent = product.barcode;
            valId.textContent = String(product.id).padStart(5, '0'); // 5桁に揃えて表示
            valPrice.textContent = product.price;
            valTaxin.textContent = product.tax_rate;
            valGenre.textContent = product.category;
            
            // 表示を切り替えて削除ボタンを押せるようにする
            noDataMessage.style.display = 'none';
            detailGrid.style.display = 'grid';
            btnDelete.disabled = false;
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

// 「削除する」ボタンが押されたときの処理
btnDelete.addEventListener('click', () => {
    if (!currentProductId) return;

    const productName = valName.textContent;
    
    // 誤操作防止の確認アラート
    const isConfirmed = confirm(`本当に「${productName}」を削除してもよろしいですか？\n※この操作は取り消せません。`);
    
    if (isConfirmed) {
        // PHP側に非同期で削除リクエストを送信
        fetch('delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id: currentProductId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`「${productName}」をデータベースから完全に削除しました。`);
                searchKeyword.value = "";
                resetScreen(); // 画面を初期状態に戻す
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('削除処理中にエラーが発生しました。');
        });
    }
});

// 画面の表示をリセット（データなし状態に戻す）関数
function resetScreen() {
    currentProductId = null;
    noDataMessage.style.display = 'block';
    detailGrid.style.display = 'none';
    btnDelete.disabled = true;
    
    valName.textContent = "";
    valBarcode.textContent = "";
    valId.textContent = "";
    valPrice.textContent = "";
    valTaxin.textContent = "";
    valGenre.textContent = "";
}