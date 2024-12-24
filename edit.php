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
$table = $_GET['table'] ?? 'stations';
$id = $_GET['id'] ?? null;

if (!$id) {
    die("必須提供 ID。");
}

// 獲取欄位名稱
$query = "DESCRIBE $table";
$stmt = $pdo->prepare($query);
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 獲取現有資料
$query = "SELECT * FROM $table WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    die("未找到對應資料。");
}

// 判斷是否提交表單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    foreach ($columns as $column) {
        if ($column !== 'id') { // 自動遞增的主鍵不需要處理
            $data[$column] = $_POST[$column] ?? null;
        }
    }

    // 構建更新語句
    $fields = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
    $sql = "UPDATE $table SET $fields WHERE id = ?";

    // 執行更新
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([...array_values($data), $id]);
        header("Location: crud.php?table=$table");
        exit;
    } catch (PDOException $e) {
        $error = "更新失敗: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯 <?= htmlspecialchars($table) ?> 的資料</title>
</head>
<body>
    <h1>編輯 <?= htmlspecialchars($table) ?> 的資料</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <?php foreach ($columns as $column): ?>
            <?php if ($column !== 'id'): // 忽略主鍵 ?>
                <div>
                    <label for="<?= htmlspecialchars($column) ?>"><?= htmlspecialchars($column) ?>:</label>
                    <input type="text" name="<?= htmlspecialchars($column) ?>" id="<?= htmlspecialchars($column) ?>" value="<?= htmlspecialchars($record[$column]) ?>" required>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit">保存</button>
    </form>
    <a href="crud.php?table=<?= htmlspecialchars($table) ?>">返回</a>
</body>
</html>
