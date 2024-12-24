<?php
// 資料庫設定
$host = 'localhost';
$dbname = 'bike_dataset';
$username = 'root';
$password = '';

// 連線資料庫
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}

// 取得目標資料表
$table = $_GET['table'] ?? 'station_list';

// 讀取資料
$query = "SELECT * FROM $table";
$stmt = $pdo->prepare($query);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 列出欄位名稱
$columns = array_keys($data[0] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理 <?= htmlspecialchars($table) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>管理 <?= htmlspecialchars($table) ?></h1>
    <table border="1">
        <thead>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th><?= htmlspecialchars($column) ?></th>
                <?php endforeach; ?>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?= htmlspecialchars($cell) ?></td>
                    <?php endforeach; ?>
                    <td>
                        <a href="edit.php?table=<?= $table ?>&id=<?= $row['id'] ?>">編輯</a>
                        <a href="delete.php?table=<?= $table ?>&id=<?= $row['id'] ?>" onclick="return confirm('確定刪除?')">刪除</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="add.php?table=<?= $table ?>">新增資料</a>
</body>
</html>
