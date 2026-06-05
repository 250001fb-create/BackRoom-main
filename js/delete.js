// HTML要素の取得
const searchForm = document.getElementById('search-form');
const searchType = document.getElementById('search-type');
const searchKeyword = document.getElementById('search-keyword');

const noDataMessage = document.getElementById('no-data-message');
const detailSectionWrapper = document.getElementById('detail-section-wrapper');
const detailGrid = document.getElementById('detail-grid');
const tableContainer = document.getElementById('table-container');
const resultsTbody = document.getElementById('results-tbody');

const valName = document.getElementById('val-name');
const valBarcode = document.getElementById('val-barcode');
const valId = document.getElementById('val-id');
const valPrice = document.getElementById('val-price');
const valTaxin = document.getElementById('val-taxin');
const valGenre = document.getElementById('val-genre');

const btnDelete = document.getElementById('btn-delete');
const btnTopBack = document.getElementById('btn-top-back'); // 🌟 追加：戻るボタン

// 状態管理用の変数
let currentProductId = null;
let isDetailView = false; // 🌟 追加：現在詳細画面を開いているかどうかの判定

// 🌟 「戻る/一覧に戻る」ボタンの制御
btnTopBack.addEventListener('click', (e) => {
    // 詳細画面を表示しているときだけ、本来のリンク移動（戻る）をキャンセルして一覧画面に戻す
    if (isDetailView) {
        e.preventDefault(); 
        
        // 詳細を隠してテーブル（一覧）を表示
        detailSectionWrapper.style.display = 'none';
        tableContainer.style.display = 'block';
        btnDelete.disabled = true; // 削除ボタンは無効化
        
        // ボタンのテキストと状態を元に戻す
        btnTopBack.textContent = '戻る';
        isDetailView = false;
    }
});

// 「検索」フォームが送信されたときの処理
searchForm.addEventListener('submit', (e) => {
    e.preventDefault(); 

    const type = searchType.value;
    let keyword = searchKeyword.value.trim();

    if (!keyword) {
        alert('検索キーワードを入力してください。');
        return;
    }

    if (type === 'id') {
        const parsedNum = parseInt(keyword, 10);
        if (isNaN(parsedNum)) {
            alert('商品IDは数字で入力してください。');
            return;
        }
        keyword = parsedNum;
    }

    fetch('delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'search', type: type, keyword: keyword })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultsTbody.innerHTML = '';

            data.products.forEach(product => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${String(product.id).padStart(5, '0')}</td>
                    <td style="text-align: left; font-weight: bold;">${product.name}</td>
                    <td>${product.category}</td>
                    <td>${product.price}</td>
                    <td><button type="button" class="btn-select">選択</button></td>
                `;

                const selectBtn = tr.querySelector('.btn-select');
                selectBtn.addEventListener('click', () => showDetail(product));
                
                resultsTbody.appendChild(tr);
            });

            // 検索直後はテーブルを表示
            noDataMessage.style.display = 'none';
            detailGrid.style.display = 'none';
            detailSectionWrapper.style.display = 'none';
            tableContainer.style.display = 'block';
            btnDelete.disabled = true; 
            
            // 🌟 検索し直した場合は「戻る」状態にリセット
            btnTopBack.textContent = '戻る';
            isDetailView = false;

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

// リストから商品が「選択」されたときに詳細を表示する関数
function showDetail(product) {
    currentProductId = product.id; 

    valName.textContent = product.name;
    valBarcode.textContent = product.barcode;
    valId.textContent = String(product.id).padStart(5, '0');
    valPrice.textContent = product.price;
    valTaxin.textContent = product.tax_rate;
    valGenre.textContent = product.category;
    
    tableContainer.style.display = 'none';
    detailSectionWrapper.style.display = 'flex';
    noDataMessage.style.display = 'none';
    detailGrid.style.display = 'grid';
    btnDelete.disabled = false; 

    // 🌟 詳細画面に入ったらボタンを「一覧に戻る」に変更
    btnTopBack.textContent = '一覧に戻る';
    isDetailView = true;
}

// 「削除する」ボタンが押されたときの処理
btnDelete.addEventListener('click', () => {
    if (!currentProductId) return;

    const productName = valName.textContent;
    
    const isConfirmed = confirm(`本当に「${productName}」を削除してもよろしいですか？\n※この操作は取り消せません。`);
    
    if (isConfirmed) {
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
                resetScreen(); 
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
    
    tableContainer.style.display = 'none';
    detailSectionWrapper.style.display = 'flex';
    noDataMessage.style.display = 'block';
    detailGrid.style.display = 'none';
    btnDelete.disabled = true;
    
    valName.textContent = "";
    valBarcode.textContent = "";
    valId.textContent = "";
    valPrice.textContent = "";
    valTaxin.textContent = "";
    valGenre.textContent = "";

    // 🌟 リセット時は「戻る」状態に戻す
    btnTopBack.textContent = '戻る';
    isDetailView = false;
}