document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('detailModal');
    const closeModal = document.getElementById('closeModal');
    
    // ==========================================
    // 1. 詳細ボタンがクリックされた時の処理
    // ==========================================
    document.querySelectorAll('.btn-detail').forEach(button => {
        button.addEventListener('click', function() {
            const saleId = this.getAttribute('data-id');
            
            // 基本情報をポップアップにセット
            document.getElementById('modalSaleId').textContent = String(saleId).padStart(5, '0');
            document.getElementById('modalTime').textContent = this.getAttribute('data-time');
            document.getElementById('modalMethod').textContent = this.getAttribute('data-method');
            document.getElementById('modalTotal').textContent = this.getAttribute('data-total');
            
            const tbody = document.getElementById('modalTableBody');
            tbody.innerHTML = '<tr><td colspan="4">読み込み中...</td></tr>';
            
            // ポップアップを表示
            modal.style.display = 'flex';
            
            // 裏側でデータベース（sale_details）から内訳を取得
            fetch(`get_sale_details.php?sale_id=${saleId}`)
                .then(response => response.json())
                .then(data => {
                    tbody.innerHTML = '';
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4">内訳データがありません。</td></tr>';
                        return;
                    }
                    
                    // 取得した注文内訳をテーブルに展開
                    data.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td style="text-align:left;">${escapeHTML(item.item_name)}</td>
                            <td>¥${Number(item.price).toLocaleString()}</td>
                            <td>${item.quantity}</td>
                            <td>¥${(Number(item.price) * Number(item.quantity)).toLocaleString()}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(err => {
                    tbody.innerHTML = '<tr><td colspan="4" style="color:red;">データの取得に失敗しました。</td></tr>';
                });
        });
    });

    // モーダルを閉じる処理（×ボタン、または背景クリック）
    closeModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
    
    // 安全対策（XSS防止）のエスケープ処理
    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g, tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag));
    }

    // ==========================================
    // 🌟 2. 検索機能（絞り込み処理）を強化
    // ==========================================
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('history-search');

    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault(); // 画面リロードを防ぐ
            
            const keyword = searchInput.value.trim(); // 入力されたキーワード
            const rows = document.querySelectorAll('.history-table tbody tr'); 

            rows.forEach(row => {
                const idCell = row.querySelector('td:first-child');
                
                if (idCell) {
                    // 表の中のIDを取得し、前後の不要な空白を確実に消去
                    const idText = idCell.textContent.trim(); 
                    
                    // 検索欄が空っぽなら全部表示して終了
                    if (keyword === '') {
                        row.style.display = '';
                        return;
                    }

                    // 入力値と表のIDを、両方とも「純粋な数字（10進数）」に変換する
                    // （例: "00010" も "10" も、両方とも数値の 10 として認識させる）
                    const searchNum = parseInt(keyword, 10);
                    const idNum = parseInt(idText, 10);

                    // 数字として完全に一致するか、文字として含まれていれば表示
                    if (searchNum === idNum || idText.includes(keyword)) {
                        row.style.display = ''; 
                    } else {
                        row.style.display = 'none'; 
                    }
                }
            });
        });
    }
});