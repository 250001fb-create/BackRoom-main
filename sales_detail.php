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

    <script src="js/sales_detail.js"></script>
</body>
</html>