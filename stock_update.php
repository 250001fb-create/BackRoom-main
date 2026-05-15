<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>在庫数変更 - バックルーム</title>
    <link rel="stylesheet" href="style/stock_update.css">
</head>
<body>
    <div class="main-container">
        <a href="stock_select.php" class="btn-back">戻る</a>

        <div class="search-section">
            <div class="search-container">
                <select id="search-type" class="search-select">
                    <option value="id">商品ID</option>
                    <option value="name">商品名</option>
                </select>
                <input type="text" id="search-keyword" placeholder="キーワードを入力" class="search-input">
                <button id="btn-search" class="btn-search">検索</button>
            </div>
        </div>

        <div id="result-section" class="result-section hidden">
            <div class="table-container">
                <table class="result-table">
                    <thead>
                        <tr>
                            <th>商品ID</th>
                            <th>商品名</th>
                            <th>現在の在庫</th>
                            <th>変更後の在庫</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="result-body">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
         document.addEventListener("DOMContentLoaded", function() {
            const searchBtn = document.getElementById("btn-search");
            const resultSection = document.getElementById("result-section");
            const resultBody = document.getElementById("result-body");

            searchBtn.addEventListener("click", function() {
                const searchType = document.getElementById("search-type").value;
                const keyword = document.getElementById("search-keyword").value;

                if (keyword.trim() === "") {
                    alert("検索キーワードを入力してください");
                    return;
                }

                // 一旦テーブルの中身を空にする
                resultBody.innerHTML = "";

                // テスト用のダミーデータ生成
                if (searchType === "id") {
                    // ID検索の場合は1件だけヒットしたと仮定
                    resultBody.innerHTML = `
                        <tr>
                            <td>${keyword}</td>
                            <td>テスト商品A</td>
                            <td>50</td>
                            <td><input type="number" class="inline-input" placeholder="数"></td>
                            <td><button class="btn-inline-update" onclick="alert('更新しました')">更新</button></td>
                        </tr>
                    `;
                } else if (searchType === "name") {
                    // 名前検索の場合は複数件ヒットしたと仮定
                    resultBody.innerHTML = `
                        <tr>
                            <td>10001</td>
                            <td>天然${keyword}</td>
                            <td>50</td>
                            <td><input type="number" class="inline-input" placeholder="数"></td>
                            <td><button class="btn-inline-update" onclick="alert('更新しました')">更新</button></td>
                        </tr>
                        <tr>
                            <td>10002</td>
                            <td>炭酸${keyword}</td>
                            <td>20</td>
                            <td><input type="number" class="inline-input" placeholder="数"></td>
                            <td><button class="btn-inline-update" onclick="alert('更新しました')">更新</button></td>
                        </tr>
                    `;
                }

                // 結果エリアを表示する
                resultSection.classList.remove("hidden");
            });
        });

        let currentItemId = null;

        document.getElementById("btn-search").addEventListener("click", async () => {
            const id = document.getElementById("target-id").value;
            if(!id) return alert("IDを入力してください");

            // 商品情報を取得するAPI(後述のget_item.php)を叩く
            const res = await fetch(`get_item.php?id=${id}`);
            const data = await res.json();

            if (data.success) {
                currentItemId = id;
                document.getElementById("res-name").textContent = data.item.item_name;
                document.getElementById("res-stock").textContent = data.item.stock_quantity;
                document.getElementById("result-section").classList.remove("hidden");
            } else {
                alert("商品が見つかりません。商品ID(1, 2...)を入力してください。");
            }
        });

        document.getElementById("btn-update-exec").addEventListener("click", async () => {
            const newStock = document.getElementById("new-stock").value;
            if(newStock === "") return alert("個数を入力してください");

            const res = await fetch('update_stock_exec.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: currentItemId, stock: newStock})
            });
            const data = await res.json();
            if (data.success) {
                alert("在庫数を更新しました！");
                location.reload();
            } else {
                alert("更新に失敗しました: " + data.message);
            }
        });
    </script>
</body>
</html>