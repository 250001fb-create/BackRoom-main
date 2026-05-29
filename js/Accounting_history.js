document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('detailModal');
    const closeModal = document.getElementById('closeModal');
    
    // 詳細ボタンがクリックされた時の処理
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
            
            // ポップアップを表示（CSS側で .modal { display: none; } に設定されているためflexで開く）
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
});