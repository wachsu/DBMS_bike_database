<?php
// fetch_station_details.php

// 設定回應內容型別為 JSON 並輸出
header('Content-Type: application/json');

// 獲取傳入的站點名稱和日期參數
// 因為前面對 station_name 編碼，所以現在要對 station_name 解碼 
$station_name = urldecode($_GET['station_name']);
$date = $_GET['date'];

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

    // 根據傳入站點名稱及日期查詢借車和還車數量
    $sql = "SELECT pickup_counts, dropoff_counts FROM usage_frequency WHERE station_name = :station_name AND DATE(date) = :date";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':station_name', $station_name, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    $usage_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // 根據傳入日期查詢天氣資料
    $weather_sql = "SELECT temp, humidity, conditions FROM weather WHERE DATE(datetime) = :date";
    $weather_stmt = $pdo->prepare($weather_sql);
    $weather_stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $weather_stmt->execute();
    $weather_data = $weather_stmt->fetch(PDO::FETCH_ASSOC);

    // 返回站點使用資料及天氣資料
    $response = [
        'usage_data' => $usage_data,
        'weather' => $weather_data
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    // 如果資料庫連接失敗，輸出錯誤訊息
    echo json_encode(['error' => $e->getMessage()]);
}

// 關閉資料庫連接
$pdo = null;
?>
