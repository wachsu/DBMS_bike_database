<?php
// 資料庫設定
$host = 'localhost';
$dbname = 'bike_rental';
$username = 'root';
$password = '';

// 連線資料庫
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}

// 獲取目標資料表和資料ID
$table = $_GET['table'] ?? null;
$id1 = $_GET[rawurldecode('id1')] ?? null;
$id2 = $_GET[rawurldecode('id2')] ?? null;

// 限制允許操作的資料表名稱
$allowedTables = ['station_list', 'daily_rent_detail', 'usage_frequency'];
if (!in_array($table, $allowedTables)) {
    die("無效的資料表名稱");
}

// 確定主鍵欄位名稱
if ($table == 'station_list') {
    $column1 = 'station_id';
    $column2 = 'station_name';
} elseif ($table == 'daily_rent_detail') {
    $column1 = 'ride_id';
    $column2 = 'started_at';
} elseif ($table == 'usage_frequency') {
    $column1 = 'date';
    $column2 = 'station_name';
} else {
    die("未知的資料表");
}

// 驗證主鍵是否存在
if (!$id1 || !$id2) {
    die("沒有提供必要的主鍵 ID");
}

// 獲取欄位名稱
$query = "DESCRIBE $table";
$stmt = $pdo->prepare($query);
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 獲取現有資料
$query = "SELECT * FROM $table WHERE $column1 = :id1 AND $column2 = :id2";
$stmt = $pdo->prepare($query);
$stmt->execute(['id1' => $id1, 'id2' => $id2]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    die("未找到對應資料。");
}

// 判斷是否提交表單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    foreach ($columns as $column) {
        $data[$column] = $_POST[$column] ?? null;
    }

    if ($table == 'daily_rent_detail') {
        $station_1 = $data['start_station_name'];
        $station_2 = $data['end_station_name'];
        $id_1 = $data['start_station_id'];
        $id_2 = $data['end_station_id'];

        
        $check_sql = "SELECT COUNT(*) FROM station_list WHERE station_name = ? and station_id = ?";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute([$station_1, $id_1]);
        $count1 = $stmt->fetchColumn();

        $check_sql = "SELECT COUNT(*) FROM station_list WHERE station_name = ? and station_id = ?";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute([$station_2, $id_2]);
        $count2 = $stmt->fetchColumn();

        if ($count1 < 1 || $count2 < 1) {
            $error = "站點不存在或站點名稱與站點ID不相符，無法編輯資料！";
        }

        if ($data['started_at']>$data['ended_at']){
            $error = "開始時間必須早於結束時間!";
        }

    } elseif ($table == 'usage_frequency') {
        $station_1 = $data['station_name'];
        $check_sql = "SELECT COUNT(*) FROM station_list WHERE station_name = ?";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute([$station_1]);
        $count = $stmt->fetchColumn();

        if ($count < 1) {
            $error = "站點不存在，無法編輯資料！";
        }        
    }

    if(empty($error)){
        // 構建更新語句
        $fields = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data)));
        $sql = "UPDATE $table SET $fields WHERE $column1 = :id1 AND $column2 = :id2";

        // 執行更新
        $stmt = $pdo->prepare($sql);
        try {
            $data['id1'] = $id1; // 添加主鍵參數
            $data['id2'] = $id2; // 添加主鍵參數
            $stmt->execute($data);
            header("Location: crud.php?table=$table");
            exit;
        } catch (PDOException $e) {
            $error = "更新失敗: " . $e->getMessage();
        }
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯 <?= htmlspecialchars($table) ?> 的資料</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>編輯 <?= htmlspecialchars($table) ?> 的資料</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <?php foreach ($columns as $column): ?>
            <div>
                <label for="<?= htmlspecialchars($column) ?>"><?= htmlspecialchars($column) ?>:</label>
                <input type="text" name="<?= htmlspecialchars($column) ?>" id="<?= htmlspecialchars($column) ?>" value="<?= htmlspecialchars($record[$column] ?? '') ?>" required>
            </div>
        <?php endforeach; ?>
        <button type="submit">保存</button>
    </form>
    <a href="crud.php?table=<?= htmlspecialchars($table) ?>">返回</a>
</body>
</html>
