<?php

// セッションの開始
session_start();

// ログインしていない（セッションに情報がない）場合は、ログイン画面へ強制リダイレクト
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// ブラウザのキャッシュを無効化するヘッダー（戻るボタン対策）
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'db.php';

// メッセージ用変数
$error_msg = '';
$success_msg = '';

// 検索された社員の情報を保持する変数
$searched_staff = null;
$search_type = isset($_GET['search-type']) ? $_GET['search-type'] : 'emp_number';
$keyword = isset($_GET['search-keyword']) ? trim($_GET['search-keyword']) : '';

// 1. 【検索処理】URLに検索キーワードがある場合
if ($keyword !== '') {
    try {
        if ($search_type === 'emp_name') {
            // 社員名（部分一致）で検索
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

// 2. 【削除処理】フォームからPOST送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $target_id = isset($_POST['target_id']) ? intval($_POST['target_id']) : 0;

    if ($target_id > 0) {
        try {
            // データベースから削除を実行
            $stmt = $pdo->prepare('DELETE FROM staff WHERE staff_id = :staff_id');
            $stmt->execute(['staff_id' => $target_id]);

            // 削除完了後は検索結果をクリアしてメッセージ表示のためにリダイレクト
            header("Location: delete_staff.php?success=1");
            exit;
        } catch (PDOException $e) {
            $error_msg = '削除失敗: ' . $e->getMessage();
        }
    }
}

// 削除完了後にURLに success=1 が付いている場合
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_msg = '社員情報を正常に削除しました。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社員情報削除画面 - バックルームコンピューター</title>
    <link rel="stylesheet" href="style/delete_staff.css">
</head>
<body>
    <div class="main-container">
        
        <div class="top-section">
            <a href="staff_edit.php" class="btn-back">戻る</a>
            <h2 class="page-title">社員情報削除</h2>
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
        
        <form action="delete_staff.php" method="GET" class="search-section">
            <select name="search-type" class="search-select">
                <option value="emp_number" <?php if ($search_type === 'emp_number') echo 'selected'; ?>>社員番号</option>
                <option value="emp_name" <?php if ($search_type === 'emp_name') echo 'selected'; ?>>社員名</option>
            </select>
            <input type="text" name="search-keyword" class="search-input" placeholder="社員番号・社員名を入力してください" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" required>
            <button type="submit" class="btn-search">検索</button>
        </form>
        
        <div class="detail-section">
            <?php if (!$searched_staff): ?>
                <div id="no-data-message" class="no-data-msg">
                    上の検索窓から社員を検索してください。ここに登録情報が表示されます。
                </div>
            <?php else: ?>
                <div id="detail-list" class="detail-wrapper">
                    <div class="detail-header">登録情報 (この画面では何も変更不可能)</div>
                    
                    <div class="detail-row">
                        <div class="detail-label">名前</div>
                        <div class="detail-value"><?php echo htmlspecialchars($searched_staff['staff_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">カタカナ</div>
                        <div class="detail-value"><?php echo htmlspecialchars($searched_staff['kana'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">社員番号</div>
                        <div class="detail-value"><?php echo htmlspecialchars($searched_staff['staff_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">システム内ID</div>
                        <div class="detail-value"><?php echo htmlspecialchars($searched_staff['staff_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($searched_staff): ?>
            <form action="delete_staff.php" method="POST" class="bottom-section" onsubmit="return confirm('本当にこの社員情報を削除しますか？\nこの操作は取り消せません。');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="target_id" value="<?php echo $searched_staff['staff_id']; ?>">
                <button type="submit" id=\"btn-delete\" class="btn-delete">削除する</button>
            </form>
        <?php else: ?>
            <div class="bottom-section">
                <button type="button" class="btn-delete" disabled style="background-color: #ccc; cursor: not-allowed;">削除する</button>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>