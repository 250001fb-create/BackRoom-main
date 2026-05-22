<?php
// 1. セッションの開始
session_start();
require_once 'db.php';

$error_message = '';

// 2. ログインボタン（POST送信）が押されたときの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // HTMLフォームのname属性（employee_id, password）から値を受け取る
    $input_id = $_POST['employee_id'] ?? '';
    $input_pass = $_POST['password'] ?? '';

    if (!empty($input_id) && !empty($input_pass)) {
        try {
            // staffテーブルの staff_number 列を検索
            $stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_number = ?");
            $stmt->execute([$input_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // スタッフが存在し、DBのパスワードハッシュ列（password_hash）が空ではない場合
            if ($user && !empty($user['password_hash'])) {
                
                // 【今度こそ確定】DBの「password_hash」列と画面からの入力を安全に比較
                if (password_verify($input_pass, $user['password_hash'])) {
                    
                    // セッションにログイン情報を記録
                    $_SESSION['loggedin'] = true;
                    $_SESSION['staff_number'] = $user['staff_number'];
                    $_SESSION['staff_name'] = $user['staff_name'];

                    // メニュー画面（menu.php）へ移動
                    header('Location: menu.php');
                    exit;
                    
                } else {
                    $error_message = '社員番号またはパスワードが間違っています。';
                }
            } else {
                $error_message = '社員番号またはパスワードが間違っています。';
            }
        } catch (PDOException $e) {
            $error_message = 'データベース接続エラーが発生しました。';
        }
    } else {
        $error_message = 'すべての項目を入力してください。';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>バックルームコンピューター - ログイン</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="login-wrapper">
        <header>
            <h1>バックルームコンピューター</h1>
            <p>社員番号とパスワードを入力してください</p>
        </header>

        <main>
            <?php if (!empty($error_message)): ?>
                <div class="error-message" style="color: red; margin-bottom: 20px; font-weight: bold;">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <input type="text" placeholder="社員番号" name="employee_id" class="big-input" required value="<?= htmlspecialchars($_POST['employee_id'] ?? '') ?>">
                <input type="password" placeholder="パスワード" name="password" class="big-input" required>
                
                <button type="submit" class="btn-login-big" style="border: none; cursor: pointer; font-family: inherit;">ログイン</button>
            </form>
        </main>
    </div>
</body>
</html>