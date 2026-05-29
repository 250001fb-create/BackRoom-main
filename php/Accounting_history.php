<?php
require_once 'db.php';

try {
    // 【修正】決済方法(payment_method)も一緒に取得するように変更
    $stmt = $pdo->query("SELECT sale_id, created_at, total_amount, payment_method FROM sales ORDER BY created_at DESC");
    $histories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DBエラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>会計履歴 - バックルーム</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/Accounting_history.css">
</head>
<body>
    <div class="main-container">
        <div class="top-section">
            <a href="menu.php" class="btn-back">戻る</a>
            <div class="search-container">
                <input type="text" placeholder="会計IDを入力" class="search-input" id="history-search">
                <button class="btn-search">検索</button>
            </div>
        </div>
        <div class="table-container">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>会計ID</th>
                        <th>時間</th>
                        <th>合計金額</th>
                        <th>支払方法</th> <th>詳細</th> </tr>
                </thead>
                <tbody>
                    <?php foreach ($histories as $h): ?>
                    <tr>
                        <td><?= sprintf('%05d', $h['sale_id']) ?></td>
                        <td><?= $h['created_at'] ?></td>
                        <td>¥<?= number_format($h['total_amount']) ?></td>
                        <td><?= htmlspecialchars($h['payment_method'] ?? '不明') ?></td> <td>
                            <button class="btn-detail" 
                                    data-id="<?= $h['sale_id'] ?>" 
                                    data-time="<?= $h['created_at'] ?>"
                                    data-method="<?= htmlspecialchars($h['payment_method'] ?? '不明') ?>"
                                    data-total="¥<?= number_format($h['total_amount']) ?>">
                                詳細
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <h2 style="margin-top:0; color:#333; font-size: 20px; border-bottom: 2px solid #146c36; padding-bottom: 8px;">注文詳細</h2>
            
            <div class="modal-meta">
                <div><strong>会計ID:</strong> <span id="modalSaleId"></span></div>
                <div><strong>日時:</strong> <span id="modalTime"></span></div>
                <div><strong>支払方法:</strong> <span id="modalMethod"></span></div>
                <div><strong>合計金額:</strong> <span id="modalTotal" style="font-weight:bold; color:#146c36;"></span></div>
            </div>
            
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th>単価</th>
                        <th>数量</th>
                        <th>小計</th>
                    </tr>
                </thead>
                <tbody id="modalTableBody">
                    </tbody>
            </table>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>