<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>売上情報・商品検索 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/sales_detail.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <a href="sales.php" class="btn-back">戻る</a>
            
            <div class="search-container" id="search-bar-box">
                <select class="search-select">
                    <option value="id">商品ID</option>
                    <option value="name">商品名</option>
                    <option value="barcode">バーコード番号</option>
                </select>
                <input type="text" id="search-input" placeholder="検索キーワードを入力">
                <button type="button" id="btn-search">検索</button>
            </div>

            <div class="product-info-header" id="product-info-box" style="display: none;">
                <div class="info-cell" id="info-id">商品ID: ------</div>
                <div class="info-cell" id="info-name">商品名: ------</div>
                <div class="info-cell" id="info-genre" style="border: none;">ジャンル: ------</div>
                <button type="button" class="btn-clear-search" id="btn-clear">✕ 解除</button>
            </div>
        </div>
        
        <div id="view-global-sales" style="display: flex; flex-direction: column; flex-grow: 1;">
            <div class="tab-group">
                <button class="tab-btn" data-period="過去1時間">過去1時間</button>
                <button class="tab-btn active" data-period="本日">本日</button>
                <button class="tab-btn" data-period="今週">今週</button>
                <button class="tab-btn" data-period="今月">今月</button>
                <button class="tab-btn" data-period="今年">今年</button>
            </div>

            <div class="content-columns">
                <div class="column-left">
                    <div class="data-card large-card">
                        <h3 class="global-sales-title">本日の売り上げ</h3>
                        <div class="data-value class-global-sales-val">145,200円</div>
                    </div>
                </div>

                <div class="column-right">
                    <div class="data-card small-card">
                        <h3 class="global-count-title">本日の会計回数</h3>
                        <div class="data-value class-global-count-val">84回</div>
                    </div>

                    <div class="data-card small-card">
                        <h3>売れているジャンル</h3>
                        <div class="data-list">
                            <p>1. 野菜</p>
                            <p>2. 肉・魚</p>
                            <p>3. 飲料</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="view-product-sales" style="display: none; flex-direction: column; flex-grow: 1;">
            <div class="product-stats-grid">
                <div class="data-card">
                    <h3 id="product-count-title">本日の売上個数</h3>
                    <div class="data-value" id="product-count-val">0個</div>
                </div>
                <div class="data-card">
                    <h3 id="product-amount-title">本日の売上金額</h3>
                    <div class="data-value" id="product-amount-val">0円</div>
                </div>
            </div>

            <div class="tab-group tab-bottom">
                <button class="tab-btn" data-period="過去1時間">過去1時間</button>
                <button class="tab-btn active" data-period="本日">本日</button>
                <button class="tab-btn" data-period="今週">今週</button>
                <button class="tab-btn" data-period="今月">今月</button>
                <button class="tab-btn" data-period="今年">今年</button>
            </div>
        </div>
        
    </div>

    <script>
        // 通常時のダミーデータ
        const globalDummyData = {
            "過去1時間": { sales: "12,500円", count: "8回" },
            "本日":      { sales: "145,200円", count: "84回" },
            "今週":      { sales: "920,400円", count: "512回" },
            "今月":      { sales: "3,840,000円", count: "2,055回" },
            "今年":      { sales: "45,600,000円", count: "25,300回" }
        };

        // 商品単体検索時のダミーデータ（例: お茶 500ml が検索された想定）
        const productDummyData = {
            "過去1時間": { count: "2個", amount: "300円" },
            "本日":      { count: "14個", amount: "2,100円" },
            "今週":      { count: "85個", amount: "12,750円" },
            "今月":      { count: "340個", amount: "51,000円" },
            "今年":      { count: "4,120個", amount: "618,000円" }
        };

        // HTML要素の取得
        const searchBarBox = document.getElementById('search-bar-box');
        const productInfoBox = document.getElementById('product-info-box');
        const viewGlobalSales = document.getElementById('view-global-sales');
        const viewProductSales = document.getElementById('view-product-sales');
        
        const btnSearch = document.getElementById('btn-search');
        const btnClear = document.getElementById('btn-clear');
        const searchInput = document.getElementById('search-input');

        let currentMode = 'global'; // 'global' または 'product'
        let selectedPeriod = '本日';

        /* ----------------------------------------------------
           1. 検索ボタンクリック時の処理（単体モードへ切り替え）
        ---------------------------------------------------- */
        btnSearch.addEventListener('click', function() {
            // モードを商品単体に変更
            currentMode = 'product';
            selectedPeriod = '本日'; // モード切替時は本日を初期選択に

            // 上部ヘッダーの切り替え
            searchBarBox.style.display = 'none';
            productInfoBox.style.display = 'flex';

            // 表示するダミー商品情報をセット
            document.getElementById('info-id').textContent = "商品ID: 104201";
            document.getElementById('info-name').textContent = "商品名: 緑茶 500ml";
            document.getElementById('info-genre').textContent = "ジャンル: 飲料";

            // メインコンテンツの切り替え
            viewGlobalSales.style.display = 'none';
            viewProductSales.style.display = 'flex';

            // タブの選択状態を「本日」にリセットしてデータを反映
            resetTabActive(viewProductSales);
            updateProductView();
        });

        /* ----------------------------------------------------
           2. 解除ボタンクリック時の処理（通常モードへ戻る）
        ---------------------------------------------------- */
        btnClear.addEventListener('click', function() {
            currentMode = 'global';
            selectedPeriod = '本日';
            searchInput.value = '';

            productInfoBox.style.display = 'none';
            searchBarBox.style.display = 'flex';

            viewProductSales.style.display = 'none';
            viewGlobalSales.style.display = 'flex';

            resetTabActive(viewGlobalSales);
            updateGlobalView();
        });

        /* ----------------------------------------------------
           3. タブ切り替え処理（両方のビューに対応）
        ---------------------------------------------------- */
        const allTabBtns = document.querySelectorAll('.tab-btn');
        allTabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                selectedPeriod = this.getAttribute('data-period');
                
                // クリックされたビュー内のタブのアクティブ状態を更新
                const parentGroup = this.parentElement;
                parentGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // 現在のモードに合わせて表示内容を更新
                if (currentMode === 'global') {
                    updateGlobalView();
                } else {
                    updateProductView();
                }
            });
        });

        // タブの初期アクティブ状態を「本日」にする共通関数
        function resetTabActive(targetView) {
            const tabs = targetView.querySelectorAll('.tab-btn');
            tabs.forEach(t => {
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

        // 商品単体モードのデータ更新（プレフィックスを自動付与）
        function updateProductView() {
            // ご要望通り、左側に「本日の」「今年の」を自動で付け足します
            document.getElementById('product-count-title').textContent = selectedPeriod + "の売上個数";
            document.getElementById('product-amount-title').textContent = selectedPeriod + "の売上金額";
            
            if (productDummyData[selectedPeriod]) {
                document.getElementById('product-count-val').textContent = productDummyData[selectedPeriod].count;
                document.getElementById('product-amount-val').textContent = productDummyData[selectedPeriod].amount;
            }
        }
    </script>
</body>
</html>