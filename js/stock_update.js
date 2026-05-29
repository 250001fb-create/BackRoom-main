document.addEventListener('DOMContentLoaded', () => {
    // URLのパラメータを解析
    const urlParams = new URLSearchParams(window.location.search);
    
    // ?success=1 が含まれていたらアラートを出す
    if (urlParams.get('success') === '1') {
        alert("在庫数を更新しました");
    }
});