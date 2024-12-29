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

// 取得目標資料表與主鍵
$table = $_GET['table'] ?? '';
$key1 = $_GET[rawurldecode('id1')] ?? '';
$key2 = $_GET[rawurldecode('id2')] ?? '';

print($key1);
print($key2);
if ($table && $key1) {
    try {
        // 根據不同表處理刪除
        if ($table === 'station_list') {
            // 刪除站點，連動刪除相關記錄
            $query = "DELETE FROM daily_rent_detail WHERE (start_station_id = :key1 and start_station_name = :key2) OR (end_station_id = :key1 and end_station_name = :key2)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':key1' => $key1, ':key2' => $key2]);

            $query = "DELETE FROM usage_frequency WHERE station_name = :key2";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':key2' => $key2]);

            $query = "DELETE FROM station_list WHERE station_id = :key1 and station_name = :key2";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':key1' => $key1, ':key2' => $key2]);

        } elseif ($table === 'daily_rent_detail') {
            // 刪除租借記錄
            $query = "DELETE FROM daily_rent_detail WHERE ride_id = :key1 AND started_at = :key2";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':key1' => $key1, ':key2' => $key2]);
        } elseif ($table === 'usage_frequency') {
            // 刪除使用頻率記錄
            $query = "DELETE FROM usage_frequency WHERE date = :key1 AND station_name = :key2";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':key1' => $key1, ':key2' => $key2]);
        } else {
            throw new Exception("無效的資料表名稱！");
        }

        header("Location: crud.php?table=$table");
    } catch (Exception $e) {
        die("刪除失敗: " . $e->getMessage());
    }
} else {
    die("無效的參數！");
}
?>
