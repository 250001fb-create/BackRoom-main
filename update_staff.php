<?php

// セッションの開始
session_start();

// ログインしていない場合はログイン画面へ強制リダイレクト
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// ブラウザのキャッシュを無効化
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'db.php';

// Ajax（Fetch API）からのリクエスト処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    // =========================================
    // 1. 検索処理
    // =========================================
    if ($action === 'search') {
        $type = $input['type'] ?? '';
        $keyword = $input['keyword'] ?? '';

        if ($keyword === '') {
            echo json_encode(['success' => false, 'message' => 'キーワードを入力してください。']);
            exit;
        }

        try {
            if ($type === 'emp_name') {
                $stmt = $pdo->prepare('SELECT * FROM staff WHERE staff_name LIKE :keyword');
                $stmt->execute(['keyword' => '%' . $keyword . '%']);
            } else {
                $stmt = $pdo->prepare('SELECT * FROM staff WHERE staff_number = :keyword');
                $stmt->execute(['keyword' => $keyword]);
            }
            $staff = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($staff) {
                // セキュリティのためパスワードハッシュはJSに返さない
                unset($staff['password_hash']);
                echo json_encode(['success' => true, 'staff' => $staff]);
            } else {
                echo json_encode(['success' => false, 'message' => '該当する社員が見つかりませんでした。']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => '検索失敗: ' . $e->getMessage()]);
        }
        exit;
    }

    // =========================================
    // 2. 更新処理
    // =========================================
    if ($action === 'update') {
        $id = $input['target_id'] ?? '';
        $name = trim($input['staff_name'] ?? '');
        $kana = trim($input['kana'] ?? '');
        $number = trim($input['staff_number'] ?? '');
        $pass = $input['staff_pass'] ?? '';

        if (empty($id) || empty($number) || empty($name) || empty($kana)) {
            echo json_encode(['success' => false, 'message' => '必要な項目が入力されていません。']);
            exit;
        }

        try {
            // 社員番号の重複チェック（自分以外の人が使っていないか）
            $check_stmt = $pdo->prepare('SELECT COUNT(*) FROM staff WHERE staff_number = :staff_number AND staff_id != :staff_id');
            $check_stmt->execute(['staff_number' => $number, 'staff_id' => $id]);

            if ($check_stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'その社員番号は既に他の社員に登録されています。']);
                exit;
            }

            // パスワードが入力されているかいないかでSQLを分ける
            if ($pass !== '') {
                $password_hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE staff SET staff_name = :name, kana = :kana, staff_number = :number, password_hash = :hash WHERE staff_id = :id');
                $stmt->execute([
                    'name' => $name,
                    'kana' => $kana,
                    'number' => $number,
                    'hash' => $password_hash,
                    'id' => $id
                ]);
            } else {
                $stmt = $pdo->prepare('UPDATE staff SET staff_name = :name, kana = :kana, staff_number = :number WHERE staff_id = :id');
                $stmt->execute([
                    'name' => $name,
                    'kana' => $kana,
                    'number' => $number,
                    'id' => $id
                ]);
            }
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => '更新失敗: ' . $e->getMessage()]);
        }
        exit;
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
        
        <form id="search-form" class="search-section">
            <select id="search-type" class="search-select">
                <option value="emp_number">社員番号</option>
                <option value="emp_name">社員名</option>
            </select>
            <input type="text" id="search-keyword" class="search-input" placeholder="検索キーワードを入力してください" autocomplete="off" required>
            <button type="submit" class="btn-search">検索</button>
        </form>
        
        <div id="no-data-message" class="no-data-msg">
            変更したい社員を上の検索窓から検索してください。
        </div>

        <div id="update-form-area" class="update-form-area" style="display: none;">
            <form id="update-form">
                <div class="form-wrapper">
                    
                    <div class="form-row">
                        <label>名前</label>
                        <input type="text" id="form-name" class="editable-input" placeholder="名前を入力" required>
                    </div>
                    
                    <div class="form-row">
                        <label>カタカナ</label>
                        <input type="text" id="form-kana" class="editable-input" placeholder="カタカナを入力" required>
                    </div>
                    
                    <div class="form-row">
                        <label>社員番号</label>
                        <input type="text" id="form-number" class="editable-input" placeholder="社員番号を入力" required>
                    </div>
                    
                    <div class="form-row">
                        <label>パスワード</label>
                        <input type="password" id="form-pass" class="editable-input" placeholder="新しいパスワード（変更する場合のみ入力）">
                    </div>

                </div>

                <div class="bottom-btn-wrapper">
                    <button type="submit" id="btn-submit" class="btn-submit-large">更新する</button>
                </div>
            </form>
        </div>

    </div>

    <script src="js/update_staff.js"></script>
</body>
</html>