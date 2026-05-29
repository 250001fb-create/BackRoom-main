// 天気情報の取得（気象庁API）
fetch('https://www.jma.go.jp/bosai/forecast/data/forecast/140000.json')
    .then(response => response.json())
    .then(data => {
        const weatherText = data[0].timeSeries[0].areas[0].weathers[0];
        // 全角スペースを半角に変換して表示
        document.getElementById('weather-info').textContent = weatherText.replace(/　/g, ' ');
    })
    .catch(error => {
        console.error('Weather Fetch Error:', error);
        document.getElementById('weather-info').textContent = '取得失敗';
    });