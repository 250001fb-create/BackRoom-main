// 全体用ダミーデータ
const globalDummyData = {
    "本日": { sales: "¥45,000", count: "23回" },
    "今週": { sales: "¥320,000", count: "154回" },
    "今月": { sales: "¥1,250,000", count: "612回" },
    "今年": { sales: "¥14,800,000", count: "7,340回" }
};

// 商品単体用ダミーデータ
const productDummyData = {
    "本日": { count: "12個", amount: "¥2,400" },
    "今週": { count: "84個", amount: "¥16,800" },
    "今月": { count: "310個", amount: "¥62,000" },
    "今年": { count: "3,540個", amount: "¥708,000" }
};

let selectedPeriod = "本日"; // デフォルトの選択期間

// HTML要素の取得
const tabButtons = document.querySelectorAll('.tab-btn');
const btnSearch = document.getElementById('btn-search');
const btnClear = document.getElementById('btn-clear');
const searchBarBox = document.getElementById('search-bar-box');
const productInfoBox = document.getElementById('product-info-box');

const globalCardBox = document.getElementById('global-card-box');
const productCardBox = document.getElementById('product-card-box');

// タブ（期間変更）切り替えイベント
tabButtons.forEach(button => {
    button.addEventListener('click', function() {
        tabButtons.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        selectedPeriod = this.getAttribute('data-period');
        
        // 表示中のモードに応じてデータを更新
        if (productInfoBox.style.display === 'none') {
            updateGlobalView();
        } else {
            updateProductView();
        }
    });
});

// 検索ボタンクリック（商品単体モードへ移行）
btnSearch.addEventListener('click', () => {
    const inputVal = document.getElementById('search-input').value.trim();
    if (!inputVal) {
        alert('キーワードを入力してください。');
        return;
    }

    // 本来はここでDBに検索をかけますが、現在はデモ用にヘッダーを上書き
    document.getElementById('info-id').textContent = "商品ID: 00012";
    document.getElementById('info-name').textContent = `商品名: ${inputVal}`;
    document.getElementById('info-genre').textContent = "ジャンル: 飲料";

    // 検索バーを隠して商品情報を表示
    searchBarBox.style.display = 'none';
    productInfoBox.style.display = 'flex';

    // 表示するカードを通常用から商品単体用に切り替え
    globalCardBox.style.display = 'none';
    productCardBox.style.display = 'grid';

    updateProductView();
});

// ✕（解除）ボタンクリック（通常モードへ戻る）
btnClear.addEventListener('click', () => {
    document.getElementById('search-input').value = "";
    
    productInfoBox.style.display = 'none';
    searchBarBox.style.display = 'flex';

    productCardBox.style.display = 'none';
    globalCardBox.style.display = 'grid';

    // タブの状態を「本日」にリセット
    selectedPeriod = "本日";
    resetTabs();
    updateGlobalView();
});

// タブを「本日」のアクティブ状態に戻す関数
function resetTabs() {
    tabButtons.forEach(t => {
        if (t.getAttribute('data-period') === '本日') {
            t.classList.add('active');
        } else {
            t.classList.remove('active');
        }
    });
}

// 通常モードのデータ更新
function updateGlobalView() {
    document.querySelector('.global-sales-title').textContent = selectedPeriod + "の売り上げ";
    document.querySelector('.global-count-title').textContent = selectedPeriod + "の会計回数";
    
    if (globalDummyData[selectedPeriod]) {
        document.querySelector('.class-global-sales-val').textContent = globalDummyData[selectedPeriod].sales;
        document.querySelector('.class-global-count-val').textContent = globalDummyData[selectedPeriod].count;
    }
}

// 商品単体モードのデータ更新
function updateProductView() {
    document.getElementById('product-count-title').textContent = selectedPeriod + "の売上個数";
    document.getElementById('product-amount-title').textContent = selectedPeriod + "の売上金額";
    
    if (productDummyData[selectedPeriod]) {
        document.getElementById('product-count-val').textContent = productDummyData[selectedPeriod].count;
        document.getElementById('product-amount-val').textContent = productDummyData[selectedPeriod].amount;
    }
}