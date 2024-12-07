<?php
// fetch_station_details.php

// 獲取傳入的站點名稱和日期參數
// 因為前面對 station_name 編碼，所以現在要對 station_name 解碼 
$station_name = $_GET[urldecode('station_name')];
$date = $_GET['date'];

// 建立資料庫連接
$servername = "localhost";
$username = "root";
$password = "MySQLpassword";
$dbname = "final";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 根據傳入站點名稱及日期查詢借車和還車數量
$sql = "SELECT pickup_counts, dropoff_counts FROM usage_frequency_2024 WHERE station_name = ? AND DATE(date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $station_name, $date); // 使用字串參數
$stmt->execute();
$result = $stmt->get_result();

$usage_data = [];
if ($result->num_rows > 0) {
    $usage_data = $result->fetch_assoc();
}

// 根據傳入日期查詢天氣資料
$weather_sql = "SELECT temp, humidity, conditions FROM weather WHERE DATE(datetime) = ?";
$weather_stmt = $conn->prepare($weather_sql);
$weather_stmt->bind_param("s", $date); // 使用字串參數
$weather_stmt->execute();
$weather_result = $weather_stmt->get_result();
$weather_data = $weather_result->fetch_assoc();

// 返回站點使用資料及天氣資料
$response = [
    'usage_data' => $usage_data,
    'weather' => $weather_data
];

// 設定回應內容型別為 JSON 並輸出
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
