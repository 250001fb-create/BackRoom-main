<?php
require_once 'db.php';

// エラーメッセージ・成功メッセージ用の変数
$error_msg = '';
$success_msg = '';

// フォームがPOST送信されたときの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームデータの取得とトリミング
    $staff_name = isset($_POST['staff_name']) ? trim($_POST['staff_name']) : '';
    $staff_kana = isset($_POST['staff_kana']) ? trim($_POST['staff_kana']) : ''; // 【追加】フリガナの取得
    $staff_number = isset($_POST['staff_id']) ? trim($_POST['staff_id']) : ''; // SQLのstaff_numberに対応
    $staff_pass = isset($_POST['staff_pass']) ? $_POST['staff_pass'] : '';

    // 入力値の簡易チェック
    if ($staff_name === '' || $staff_number === '' || $staff_pass === '') {
        $error_msg = 'すべての項目を入力してください。';
    } else {
        try {
            // 1. 社員番号の重複チェック
            $check_stmt = $pdo->prepare('SELECT COUNT(*) FROM staff WHERE staff_number = :staff_number');
            $check_stmt->execute(['staff_number' => $staff_number]);
            
            if ($check_stmt->fetchColumn() > 0) {
                $error_msg = 'その社員番号は既に登録されています。';
            } else {
                // 2. パスワードのハッシュ化
                $password_hash = password_hash($staff_pass, PASSWORD_DEFAULT);

                // 3. データベースへインサート
                // ※SQLのテーブル定義に合わせて staff_number, staff_name, password_hash を登録します
                $stmt = $pdo->prepare('INSERT INTO staff (staff_number, staff_name, kana, password_hash) VALUES (:staff_number, :staff_name, :kana, :password_hash)');
                $stmt->execute([
                    'staff_number'  => $staff_number,
                    'staff_name'    => $staff_name,
                    'kana'          => $staff_kana, // SQL文の :kana としっかり連動します
                    'password_hash' => $password_hash
                ]);

                $success_msg = '社員の登録が完了しました！';
            }
        } catch (PDOException $e) {
            $error_msg = 'データベースエラー: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社員登録 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/new_staff.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <a href="staff_edit.php" class="btn-back">戻る</a>
            <h2 class="page-title">社員登録</h2>
            <div style="width: 82px;"></div>
        </div>

        <?php if ($error_msg !== ''): ?>
            <div style="color: red; text-align: center; margin-bottom: 15px; font-weight: bold;">
                <?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_msg !== ''): ?>
            <div style="color: green; text-align: center; margin-bottom: 15px; font-weight: bold;">
                <?php echo htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="new_staff.php" method="POST">
            <div class="form-wrapper">
                <div class="form-group">
                    <label for="staff_name">名前</label>
                    <input type="text" id="staff_name" name="staff_name" placeholder="例: 山田 太郎" required>
                </div>
                <div class="form-group">
                    <label for="staff_kana">読み</label>
                    <input type="text" id="staff_kana" name="staff_kana" placeholder="例: ヤマダ タロウ" required>
                </div>
                <div class="form-group">
                    <label for="staff_id">社員番号</label>
                    <input type="text" id="staff_id" name="staff_id" placeholder="例: 1001" required>
                </div>
                <div class="form-group">
                    <label for="staff_pass">パスワード</label>
                    <input type="password" id="staff_pass" name="staff_pass" placeholder="パスワードを入力" required>
                </div>
            </div>

            <div class="bottom-btn-wrapper">
                <button type="submit" class="btn-submit-large">登録</button>
            </div>
        </form>

    </div>
</body>
</html>