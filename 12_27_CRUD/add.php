<?php
// 資料庫設定
$host = 'localhost';
$dbname = 'final';
$username = 'root';
$password = 'MySQLpassword';

// 連線資料庫
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}

// 獲取目標資料表
$table = $_GET['table'] ?? 'stations';

// 獲取欄位名稱
$query = "DESCRIBE $table";
$stmt = $pdo->prepare($query);
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);


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
            $error = "站點不存在或站點名稱與站點ID不相符，無法新增資料！";
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
            $error = "站點不存在，無法新增資料！";
        }        
    }
    
    if(empty($error)){
        // 插入資料
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $fields = implode(',', array_keys($data));
        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";

        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute(array_values($data));
            header("Location: crud.php?table=$table");
            exit;
        } catch (PDOException $e) {
            $error = "新增失敗: " . $e->getMessage();
        }
    }
    
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增資料到 <?= htmlspecialchars($table) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>新增資料到 <?= htmlspecialchars($table) ?></h1>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <?php foreach ($columns as $column): ?>
            <div>
                <label for="<?= htmlspecialchars($column) ?>"><?= htmlspecialchars($column) ?>:</label>
                <input type="text" name="<?= htmlspecialchars($column) ?>" id="<?= htmlspecialchars($column) ?>" required>
            </div>
        <?php endforeach; ?>
        <button type="submit">新增</button>
    </form>

    <a href="crud.php?table=<?= htmlspecialchars($table) ?>">返回</a>
</body>
</html>
