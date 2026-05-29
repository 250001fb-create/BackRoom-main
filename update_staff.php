<?php
require_once 'db.php';

// メッセージ用変数
$error_msg = '';
$success_msg = '';

// 検索された社員の情報を保持する変数
$searched_staff = null;
// デフォルトの検索タイプを「emp_number（社員番号）」に設定
$search_type = isset($_GET['search-type']) ? $_GET['search-type'] : 'emp_number';
$keyword = isset($_GET['search-keyword']) ? trim($_GET['search-keyword']) : '';

// 1. 【検索処理】URLに検索キーワードがある場合
if ($keyword !== '') {
    try {
        if ($search_type === 'emp_name') {
            // 【変更】社員名（部分一致）で検索
            $stmt = $pdo->prepare('SELECT * FROM staff WHERE staff_name LIKE :staff_name');
            $stmt->execute(['staff_name' => '%' . $keyword . '%']);
        } else {
            // 社員番号（1001など）で完全一致検索
            $stmt = $pdo->prepare('SELECT * FROM staff WHERE staff_number = :staff_number');
            $stmt->execute(['staff_number' => $keyword]);
        }
        $searched_staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$searched_staff) {
            $error_msg = '該当する社員が見つかりませんでした。';
        }
    } catch (PDOException $e) {
        $error_msg = '検索失敗: ' . $e->getMessage();
    }
}

// 2. 【更新処理】フォームからPOST送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $target_id = isset($_POST['target_id']) ? intval($_POST['target_id']) : 0;
    $new_number = isset($_POST['staff_number']) ? trim($_POST['staff_number']) : '';
    $new_pass = isset($_POST['staff_pass']) ? $_POST['staff_pass'] : '';

    if ($target_id > 0 && $new_number !== '') {
        try {
            // 社員番号の重複チェック（自分以外の人が使っていないか）
            $check_stmt = $pdo->prepare('SELECT COUNT(*) FROM staff WHERE staff_number = :staff_number AND staff_id != :staff_id');
            $check_stmt->execute(['staff_number' => $new_number, 'staff_id' => $target_id]);

            if ($check_stmt->fetchColumn() > 0) {
                $error_msg = 'その社員番号は既に他の社員に登録されています。';
                // 画面入力を維持するため、再度データを取得し直す
                $stmt = $pdo->prepare('SELECT * FROM staff WHERE staff_id = :staff_id');
                $stmt->execute(['staff_id' => $target_id]);
                $searched_staff = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // パスワードが入力されているかいないかでSQLを分ける
                if ($new_pass !== '') {
                    // パスワードも更新する（ハッシュ化）
                    $password_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE staff SET staff_number = :staff_number, password_hash = :password_hash WHERE staff_id = :staff_id');
                    $stmt->execute([
                        'staff_number' => $new_number,
                        'password_hash' => $password_hash,
                        'staff_id'     => $target_id
                    ]);
                } else {
                    // パスワードはそのまま、社員番号だけ更新
                    $stmt = $pdo->prepare('UPDATE staff SET staff_number = :staff_number WHERE staff_id = :staff_id');
                    $stmt->execute([
                        'staff_number' => $new_number,
                        'staff_id'     => $target_id
                    ]);
                }
                
                // 更新完了後、完了メッセージをつけてリダイレクト（二重送信防止）
                header("Location: update_staff.php?search-type=" . urlencode($search_type) . "&search-keyword=" . urlencode($keyword) . "&success=1");
                exit;
            }
        } catch (PDOException $e) {
            $error_msg = '更新失敗: ' . $e->getMessage();
        }
    }
}

// URLに success=1 が付いている場合は成功メッセージを出す
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_msg = '社員情報を更新しました！';
    // 画面に最新の状態を表示するため再読込
    if ($keyword !== '') {
        if ($search_type === 'emp_name') {
            $stmt = $pdo->prepare('SELECT * FROM staff WHERE staff_name LIKE :staff_name');
            $stmt->execute(['staff_name' => '%' . $keyword . '%']);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM staff WHERE staff_number = :staff_number');
            $stmt->execute(['staff_number' => $keyword]);
        }
        $searched_staff = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社員情報更新画面 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/update_staff.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <a href="staff_edit.php" class="btn-back">戻る</a>
            <h2 class="page-title">社員情報更新</h2>
            <div style="width: 82px;"></div>
        </div>
        
        <form action="update_staff.php" method="GET" class="search-section">
            <select name="search-type" class="search-select">
                <option value="emp_number" <?php if ($search_type === 'emp_number') echo 'selected'; ?>>社員番号</option>
                <option value="emp_name" <?php if ($search_type === 'emp_name') echo 'selected'; ?>>社員名</option>
            </select>
            <input type="text" name="search-keyword" class="search-input" placeholder="検索キーワードを入力してください" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" required>
            <button type="submit" class="btn-search">検索</button>
        </form>
        
        <?php if (!$searched_staff): ?>
            <div id="no-data-message" class="no-data-msg">
                変更したい社員を上の検索窓から検索してください。
            </div>
        <?php else: ?>
            <div id="update-form-area" class="update-form-area">
                <form action="update_staff.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="target_id" value="<?php echo $searched_staff['staff_id']; ?>">
                    
                    <div class="form-wrapper">
                        
                        <div class="form-row">
                            <label>名前</label>
                            <input type="text" class="readonly-input" readonly value="<?php echo htmlspecialchars($searched_staff['staff_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        
                        <div class="form-row">
                            <label>カタカナ</label>
                            <input type="text" class="readonly-input" readonly value="<?php echo htmlspecialchars($searched_staff['kana'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        
                        <div class="form-row">
                            <label>社員番号</label>
                            <input type="text" name="staff_number" value="<?php echo htmlspecialchars($searched_staff['staff_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <label>パスワード</label>
                            <input type="password" name="staff_pass" placeholder="新しいパスワード（変更する場合のみ入力）">
                        </div>

                    </div>

                    <div class="bottom-btn-wrapper">
                        <button type="submit" class="btn-submit-large">更新</button>
                    </div>
                    
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>