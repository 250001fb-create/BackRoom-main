<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫確認 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/stock_page.css">
</head>
<body>
    <div class="main-container">
        <div class="top-section">
            <a href="stock_select.php" class="btn-back">戻る</a>
            
            <div class="search-container">
                <input type="text" placeholder="商品IDの入力" class="search-input">
                <button class="btn-search">検索</button>
            </div>
        </div>
        
        <div class="table-container">
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th>商品ID</th>
                        <th>個数</th>
                        <th>ジャンル</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>商品A</td>
                        <td>10001</td>
                        <td>50</td>
                        <td>食品</td>
                    </tr>
                    <tr>
                        <td>商品B</td>
                        <td>10002</td>
                        <td>20</td>
                        <td>日用品</td>
                    </tr>
                    <tr>
                        <td>商品C</td>
                        <td>10003</td>
                        <td>150</td>
                        <td>飲料</td>
                    </tr>
                    <tr>
                        <td>商品D</td>
                        <td>10004</td>
                        <td>5</td>
                        <td>雑貨</td>
                    </tr>
                    <tr>
                        <td>商品E</td>
                        <td>10005</td>
                        <td>80</td>
                        <td>食品</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>