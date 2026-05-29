document.addEventListener("DOMContentLoaded", function() {
    // ==========================================
    // 1. 天気情報の取得（気象庁API）
    // ==========================================
    fetch('https://www.jma.go.jp/bosai/forecast/data/forecast/140000.json')
        .then(response => response.json())
        .then(data => {
            const weatherText = data[0].timeSeries[0].areas[0].weathers[0];
            document.getElementById('weather-info').textContent = weatherText.replace(/　/g, ' ');
        })
        .catch(error => {
            console.error('Weather Fetch Error:', error);
            document.getElementById('weather-info').textContent = '取得失敗';
        });

    // ==========================================
    // 2. タブ切り替えと売上データの非同期取得
    // ==========================================
    const tabButtons = document.querySelectorAll('.tab-btn');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // タブの見た目（アクティブ状態）を切り替え
            tabButtons.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // 押されたボタンの期間（「本日」「今月」など）を取得
            const period = this.getAttribute('data-period');

            // get_sales_data.php を流用して裏側でデータを取得
            const url = `get_sales_data.php?mode=global&period=${encodeURIComponent(period)}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('データ取得エラー:', data.message);
                        return;
                    }

                    // ① 売上金額と会計回数のタイトル・数値を書き換え
                    document.getElementById('sales-title').textContent = `${period}の売り上げ`;
                    document.getElementById('sales-value').textContent = data.sales;
                    
                    document.getElementById('count-title').textContent = `${period}の会計回数`;
                    document.getElementById('count-value').textContent = data.count;

                    // ② ジャンルランキングの書き換え
                    const genreList = document.getElementById('genre-list');
                    genreList.innerHTML = ''; // 一度中身を空っぽにする

                    if (data.genres && data.genres.length > 0) {
                        // データがある場合は順番に <p> タグを作って追加していく
                        data.genres.forEach((g, index) => {
                            const p = document.createElement('p');
                            p.textContent = `${index + 1}. ${g.category}`;
                            genreList.appendChild(p);
                        });
                    } else {
                        // データがない場合の表示
                        genreList.innerHTML = '<p>データなし</p>';
                    }
                })
                .catch(error => {
                    console.error('通信エラー:', error);
                });
        });
    });
});