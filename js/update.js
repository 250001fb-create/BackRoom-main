document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('search-form');
    const searchType = document.getElementById('search-type');
    const searchKeyword = document.getElementById('search-keyword');

    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault(); // 画面の再読み込みを防ぐ（Enterキーでも発動します）
            
            const type = searchType.value; // "name" か "id" か
            const keyword = searchKeyword.value.trim(); // 入力されたキーワード
            const rows = document.querySelectorAll('.items-table tbody tr');

            rows.forEach(row => {
                // 「登録されている商品がありません。」等の行はスキップ
                if (row.children.length === 1) return;

                const idCell = row.querySelector('td:nth-child(1)'); // 1列目: 商品ID
                const nameCell = row.querySelector('td:nth-child(2)'); // 2列目: 商品名
                
                if (idCell && nameCell) {
                    const idText = idCell.textContent.trim();
                    const nameText = nameCell.textContent.trim();

                    let isMatch = false;

                    // キーワードが空っぽなら全部表示する
                    if (keyword === '') {
                        isMatch = true;
                    } 
                    // 商品名で検索（部分一致）
                    else if (type === 'name') {
                        if (nameText.includes(keyword)) {
                            isMatch = true;
                        }
                    } 
                    // 商品IDで検索（完全一致・先頭の0無視）
                    else if (type === 'id') {
                        // 両方を純粋な数値に変換して比較（例："00010" と "10" を同じとみなす）
                        const searchNum = parseInt(keyword, 10);
                        const idNum = parseInt(idText, 10);
                        if (searchNum === idNum) {
                            isMatch = true;
                        }
                    }

                    // 一致すれば表示、しなければ隠す
                    row.style.display = isMatch ? '' : 'none';
                }
            });
        });
    }
});