let selectedPeriod = "本日"; // デフォルトの選択期間
let isProductMode = false;   // 現在商品検索モードかどうか（falseなら全体表示モード）

// HTML要素の取得
const tabButtons = document.querySelectorAll('.tab-btn');
const btnSearch = document.getElementById('btn-search');
const btnClear = document.getElementById('btn-clear');
const searchBarBox = document.getElementById('search-bar-box');
const productInfoBox = document.getElementById('product-info-box');

const viewGlobalSales = document.getElementById('view-global-sales');
const viewProductSales = document.getElementById('view-product-sales');

// すべての期間タブにクリックイベントを設定
tabButtons.forEach(button => {
    button.addEventListener('click', function() {
        // アクティブなタブの見た目を切り替え
        tabButtons.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // 選択された期間を取得（過去1時間、本日、今週、今月、今年）
        selectedPeriod = this.getAttribute('data-period');
        
        // 現在のモードに合わせてデータを非同期取得
        if (!isProductMode) {
            fetchGlobalSales(); // 未検索時：項目ごとの全体売上
        } else {
            fetchProductSales(); // 検索時：項目ごとの商品売上
        }
    });
});

// 検索ボタンクリック時
btnSearch.addEventListener('click', () => {
    fetchProductSales();
});

// ✕（解除）ボタンクリック時（未検索状態の「本日」に戻す）
btnClear.addEventListener('click', () => {
    document.getElementById('search-input').value = "";
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

            // 各カードのタイトルと数値を書き換え
            document.querySelector('.global-sales-title').textContent = selectedPeriod + "の売り上げ";
            document.querySelector('.global-count-title').textContent = selectedPeriod + "の会計回数";
            document.querySelector('.class-global-sales-val').textContent = data.sales;
            document.querySelector('.class-global-count-val').textContent = data.count;

            // ジャンルランキングの動的書き換え
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
    const inputVal = document.getElementById('search-input').value.trim();

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

            // 商品検索モードをONに設定
            isProductMode = true;

            // 上部商品情報の更新と表示
            document.getElementById('info-id').textContent = `商品ID: ${data.product.id}`;
            document.getElementById('info-name').textContent = `商品名: ${data.product.name}`;
            document.getElementById('info-genre').textContent = `ジャンル: ${data.product.category}`;

            searchBarBox.style.display = 'none';
            productInfoBox.style.display = 'flex';
            viewGlobalSales.style.display = 'none';
            viewProductSales.style.display = 'flex';

            // 各カードのタイトルと数値（個数・金額）を書き換え
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