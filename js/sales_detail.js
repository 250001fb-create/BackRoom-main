let selectedPeriod = "本日"; // デフォルトの選択期間
let isProductMode = false;   // 現在商品検索モードかどうか
let currentSelectedItemId = null; // 🌟追加：現在選択中の商品ID

// HTML要素の取得
const tabButtons = document.querySelectorAll('.tab-btn');
const searchForm = document.getElementById('search-form');
const searchTypeSelect = document.getElementById('search-type');
const searchInput = document.getElementById('search-input');
const btnClear = document.getElementById('btn-clear');

const searchBarBox = document.getElementById('search-form'); // 表示切替用
const productInfoBox = document.getElementById('product-info-box');

const viewGlobalSales = document.getElementById('view-global-sales');
const viewProductList = document.getElementById('view-product-list'); // 🌟追加
const productListContainer = document.getElementById('product-list-container'); // 🌟追加
const viewProductSales = document.getElementById('view-product-sales');


// ==========================================
// タブ切り替え処理
// ==========================================
tabButtons.forEach(button => {
    button.addEventListener('click', function() {
        tabButtons.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        selectedPeriod = this.getAttribute('data-period');
        
        if (!isProductMode) {
            fetchGlobalSales(); 
        } else if (currentSelectedItemId) {
            fetchProductSales(); // 既に商品が選択されている場合は再取得
        }
    });
});

// ==========================================
// 検索実行（まずは候補リストを取得）
// ==========================================
searchForm.addEventListener('submit', (e) => {
    e.preventDefault(); 
    
    const searchType = searchTypeSelect.value;
    const inputVal = searchInput.value.trim();

    if (!inputVal) {
        alert('キーワードを入力してください。');
        return;
    }

    // 全画面を一旦リセットしてリスト取得へ
    isProductMode = false;
    currentSelectedItemId = null;

    fetch('sales_detail.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'search_products', type: searchType, keyword: inputVal })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('検索エラー: ' + data.message);
            return;
        }

        const items = data.items;
        if (items.length === 0) {
            alert('該当する商品が見つかりませんでした。');
            return;
        }

        // 商品が1件だけなら即座に詳細表示、複数ならリスト表示
        if (items.length === 1) {
            selectProduct(items[0].item_id); 
        } else {
            showProductList(items);
        }
    })
    .catch(error => {
        console.error('通信エラー:', error);
        alert('商品検索に失敗しました。');
    });
});

// ==========================================
// リスト画面の描画
// ==========================================
function showProductList(items) {
    // 表示の切り替え
    viewGlobalSales.style.display = 'none';
    viewProductSales.style.display = 'none';
    searchBarBox.style.display = 'flex';
    productInfoBox.style.display = 'none';
    
    viewProductList.style.display = 'flex';
    productListContainer.innerHTML = '';

    items.forEach(item => {
        const div = document.createElement('div');
        div.className = 'product-list-item';
        div.innerHTML = `
            <div class="list-item-info">
                <span class="list-item-name">${item.item_name}</span>
                <span class="list-item-sub">ID: ${item.item_id} | ジャンル: ${item.category}</span>
            </div>
            <button class="btn-select-product">選択</button>
        `;
        // 「選択」が押されたらそのIDをセットして詳細を取得
        div.querySelector('.btn-select-product').addEventListener('click', () => selectProduct(item.item_id));
        productListContainer.appendChild(div);
    });
}

// 商品を選択した時の処理
function selectProduct(itemId) {
    currentSelectedItemId = itemId;
    fetchProductSales(); 
}

// ==========================================
// ✕解除ボタン（全体モードへ戻る）
// ==========================================
btnClear.addEventListener('click', () => {
    searchInput.value = "";
    isProductMode = false;
    currentSelectedItemId = null;
    
    productInfoBox.style.display = 'none';
    searchBarBox.style.display = 'flex';
    
    viewProductList.style.display = 'none';
    viewProductSales.style.display = 'none';
    viewGlobalSales.style.display = 'flex';

    selectedPeriod = "本日";
    tabButtons.forEach(t => {
        if (t.getAttribute('data-period') === '本日') {
            t.classList.add('active');
        } else {
            t.classList.remove('active');
        }
    });
    fetchGlobalSales();
});

// ==========================================
// 全体モードデータ取得
// ==========================================
function fetchGlobalSales() {
    const url = `get_sales_data.php?mode=global&period=${encodeURIComponent(selectedPeriod)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.success) return;

            document.querySelector('.global-sales-title').textContent = selectedPeriod + "の売り上げ";
            document.querySelector('.global-count-title').textContent = selectedPeriod + "の会計回数";
            document.querySelector('.class-global-sales-val').textContent = data.sales;
            document.querySelector('.class-global-count-val').textContent = data.count;

            const genreList = document.getElementById('global-genre-list');
            genreList.innerHTML = '';
            if (data.genres && data.genres.length > 0) {
                data.genres.forEach((g, index) => {
                    const p = document.createElement('p');
                    p.textContent = `${index + 1}. ${g.category}`;
                    genreList.appendChild(p);
                });
            } else {
                genreList.innerHTML = '<p>データなし</p>';
            }
        })
        .catch(error => console.error('通信エラー:', error));
}

// ==========================================
// 単体モードデータ取得（選択されたIDで検索）
// ==========================================
function fetchProductSales() {
    if (!currentSelectedItemId) return;

    // 🌟変更点：必ず「ID」で正確に検索させるように上書き
    const url = `get_sales_data.php?mode=product&type=id&keyword=${encodeURIComponent(currentSelectedItemId)}&period=${encodeURIComponent(selectedPeriod)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'エラーが発生しました');
                return;
            }

            isProductMode = true;

            // 商品情報をセット
            document.getElementById('info-id').textContent = `商品ID: ${data.product.id}`;
            document.getElementById('info-name').textContent = `商品名: ${data.product.name}`;
            document.getElementById('info-genre').textContent = `ジャンル: ${data.product.category}`;

            // 表示の切り替え
            searchBarBox.style.display = 'none';
            productInfoBox.style.display = 'flex'; 
            viewGlobalSales.style.display = 'none';
            viewProductList.style.display = 'none'; // リストを隠す
            viewProductSales.style.display = 'flex'; // 詳細を表示

            document.getElementById('product-count-title').textContent = selectedPeriod + "の売上個数";
            document.getElementById('product-amount-title').textContent = selectedPeriod + "の売上金額";
            document.getElementById('product-count-val').textContent = data.sales.count;
            document.getElementById('product-amount-val').textContent = data.sales.amount;
        })
        .catch(error => {
            console.error('通信エラー:', error);
            alert('データ取得に失敗しました。');
        });
}