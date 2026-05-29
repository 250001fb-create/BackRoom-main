<?php
header('Content-Type: application/json; charset=UTF-8');
require_once 'db.php';

$mode = $_GET['mode'] ?? 'global'; // 'global'(全体) または 'product'(商品別)
$period = $_GET['period'] ?? '本日';

// --- すべての項目（期間）のSQL日付条件を定義 ---
switch ($period) {
    case '過去1時間':
        $date_condition = "AND s.created_at >= NOW() - INTERVAL 1 HOUR";
        $where_global   = "created_at >= NOW() - INTERVAL 1 HOUR";
        break;
    case '今週':
        $date_condition = "AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $where_global   = "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case '今月':
        $date_condition = "AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $where_global   = "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        break;
    case '今年':
        $date_condition = "AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        $where_global   = "created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        break;
    case '本日':
    default:
        $date_condition = "AND DATE(s.created_at) = CURDATE()";
        $where_global   = "DATE(created_at) = CURDATE()";
        break;
}

try {
    if ($mode === 'global') {
        // ==========================================
        // 項目ごと（未検索時）の全体データ取得
        // ==========================================
        
        // 1. 売上金額と会計回数の集計
        $stmt = $pdo->query("SELECT SUM(total_amount) as total, COUNT(*) as count FROM sales WHERE $where_global");
        $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total = number_format($sales_data['total'] ?? 0) . '円';
        $count = number_format($sales_data['count'] ?? 0) . '回';

        // 2. 売れているジャンルランキング上位3つ
        $sql_genre = "SELECT i.category, SUM(sd.quantity) as cnt 
                      FROM sale_details sd 
                      JOIN items i ON sd.item_id = i.item_id 
                      JOIN sales s ON sd.sale_id = s.sale_id
                      WHERE $where_global
                      GROUP BY i.category 
                      ORDER BY cnt DESC 
                      LIMIT 3";
        $genre_stmt = $pdo->query($sql_genre);
        $genres = $genre_stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'sales' => $total,
            'count' => $count,
            'genres' => $genres
        ]);
        exit;

    } else if ($mode === 'product') {
        // ==========================================
        // 項目ごと（検索時）の商品別データ取得
        // ==========================================
        $search_type = $_GET['type'] ?? '';
        $keyword = $_GET['keyword'] ?? '';

        if (!$keyword) {
            echo json_encode(['success' => false, 'message' => 'キーワードが空です']);
            exit;
        }

        // 商品マスタ(items)から商品を特定
        $item_sql = "SELECT item_id, item_name, category FROM items WHERE ";
        if ($search_type === 'id') {
            $item_sql .= "item_id = :keyword";
        } elseif ($search_type === 'barcode') {
            $item_sql .= "barcode = :keyword";
        } else {
            $item_sql .= "item_name LIKE :keyword_like";
        }
        $item_sql .= " LIMIT 1";

        $stmt = $pdo->prepare($item_sql);
        if ($search_type === 'name') {
            $stmt->bindValue(':keyword_like', '%' . $keyword . '%', PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':keyword', $keyword, PDO::PARAM_STR);
        }
        $stmt->execute();
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => '商品が見つかりませんでした。']);
            exit;
        }

        // 特定した商品の指定期間内の売上個数・金額を集計
        $sales_sql = "
            SELECT 
                COALESCE(SUM(sd.quantity), 0) AS total_count,
                COALESCE(SUM(sd.quantity * sd.selling_price), 0) AS total_amount
            FROM sale_details sd
            JOIN sales s ON sd.sale_id = s.sale_id
            WHERE sd.item_id = :item_id " . $date_condition;

        $sales_stmt = $pdo->prepare($sales_sql);
        $sales_stmt->bindValue(':item_id', $product['item_id'], PDO::PARAM_INT);
        $sales_stmt->execute();
        $sales_data = $sales_stmt->fetch();

        echo json_encode([
            'success' => true,
            'product' => [
                'id' => sprintf('%05d', $product['item_id']),
                'name' => $product['item_name'],
                'category' => $product['category']
            ],
            'sales' => [
                'count' => number_format($sales_data['total_count']) . '個',
                'amount' => number_format($sales_data['total_amount']) . '円'
            ]
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DBエラーが発生しました']);
    exit;
}