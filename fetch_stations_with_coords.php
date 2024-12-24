<?php
// fetch_stations_with_coords.php

// 設定回應內容型別為 JSON
header('Content-Type: application/json');

// 建立資料庫連接
$servername = "localhost";
$username = "root";
$password = "MySQLpassword";
$dbname = "final";
$dsn = "mysql:host=127.0.0.1;dbname=$dbname";

try {
    // 使用 PDO 連接資料庫
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 查詢所有起點和終點車站的座標，因為同一個站點可能有很多個座標，所以取最小的座標位置
    $stmt = $pdo->query('
        SELECT 
            start_station_id AS station_id, 
            start_station_name AS station_name, 
            MIN(start_lat) AS lat,
            MIN(start_lng) AS lng
        FROM daily_rent_detail
        GROUP BY start_station_id, start_station_name
        UNION
        SELECT 
            end_station_id AS station_id, 
            end_station_name AS station_name, 
            MIN(end_lat) AS lat,
            MIN(end_lng) AS lng
        FROM daily_rent_detail
        GROUP BY end_station_id, end_station_name;
    ');
    // 返回所有車站的座標資料
    $stations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stations);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
