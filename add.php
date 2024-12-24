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
        if ($column !== 'id') { // 自動遞增的主鍵不需要處理
            $data[$column] = $_POST[$column] ?? null;
        }
    }

    // 構建插入語句
    $placeholders = implode(',', array_fill(0, count($data), '?'));
    $fields = implode(',', array_keys($data));
    $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";

    // 執行插入
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute(array_values($data));
        header("Location: crud.php?table=$table");
        exit;
    } catch (PDOException $e) {
        $error = "新增失敗: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增資料到 <?= htmlspecialchars($table) ?></title>
</head>
<body>
    <h1>新增資料到 <?= htmlspecialchars($table) ?></h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <?php foreach ($columns as $column): ?>
            <?php if ($column !== 'id'): // 忽略主鍵 ?>
                <div>
                    <label for="<?= htmlspecialchars($column) ?>"><?= htmlspecialchars($column) ?>:</label>
                    <input type="text" name="<?= htmlspecialchars($column) ?>" id="<?= htmlspecialchars($column) ?>" required>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit">新增</button>
    </form>
    <a href="crud.php?table=<?= htmlspecialchars($table) ?>">返回</a>
</body>
</html>
