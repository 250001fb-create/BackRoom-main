let selectedPeriod = "本日"; // デフォルトの選択期間
let isProductMode = false;   // 現在商品検索モードかどうか（falseなら全体表示モード）

// HTML要素の取得
const tabButtons = document.querySelectorAll('.tab-btn');
const searchForm = document.getElementById('search-form'); // 🌟変更：Form要素を取得
const btnClear = document.getElementById('btn-clear');
const searchBarBox = document.getElementById('search-form'); // 表示切替用
const productInfoBox = document.getElementById('product-info-box');
const searchInput = document.getElementById('search-input');

const viewGlobalSales = document.getElementById('view-global-sales');
const viewProductSales = document.getElementById('view-product-sales');

// すべての期間タブにクリックイベントを設定
tabButtons.forEach(button => {
    button.addEventListener('click', function() {
        // アクティブなタブの見た目を切り替え
        tabButtons.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // 選択された期間を取得
        selectedPeriod = this.getAttribute('data-period');
        
        // 現在のモードに合わせてデータを非同期取得
        if (!isProductMode) {
            fetchGlobalSales(); 
        } else {
            fetchProductSales(); 
        }
    });
});

// 🌟修正：Formのsubmitイベントで検索を実行（Enterキー押下時・ボタンクリック時両方対応）
searchForm.addEventListener('submit', (e) => {
    e.preventDefault(); // 画面の再読み込み（リロード）を完全に防ぐ
    fetchProductSales(); // 検索処理を実行
});

// ✕（解除）ボタンクリック時（未検索状態の「本日」に戻す）
btnClear.addEventListener('click', () => {
    searchInput.value = "";
    isProductMode = false;
    
    // 表示エリアの初期化
    productInfoBox.style.display = 'none';
    searchBarBox.style.display = 'flex';
    viewProductSales.style.display = 'none';
    viewGlobalSales.style.display = 'flex';

    // タブの位置を「本日」に戻して再取得
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

// 【全体モード】指定された項目の売上データを取得・反映
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

// 【商品単体モード】指定された項目・特定商品の売上データを取得・反映
function fetchProductSales() {
    const searchType = document.querySelector('.search-select').value;
    const inputVal = searchInput.value.trim();

    if (!inputVal) {
        alert('キーワードを入力してください。');
        return;
    }

    const url = `get_sales_data.php?mode=product&type=${searchType}&keyword=${encodeURIComponent(inputVal)}&period=${encodeURIComponent(selectedPeriod)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'エラーが発生しました');
                return;
            }

            isProductMode = true;

            // 🌟修正：商品情報をそれぞれのspan要素にセット
            document.getElementById('info-id').textContent = `商品ID: ${data.product.id}`;
            document.getElementById('info-name').textContent = `商品名: ${data.product.name}`;
            document.getElementById('info-genre').textContent = `ジャンル: ${data.product.category}`;

            searchBarBox.style.display = 'none';
            productInfoBox.style.display = 'flex'; 
            viewGlobalSales.style.display = 'none';
            viewProductSales.style.display = 'flex';

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