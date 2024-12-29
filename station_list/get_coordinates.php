<?php
// 資料庫連接設定
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydatabase";

// 創建資料庫連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查資料庫連接
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 從資料庫中選取所有位置資料
$sql = "SELECT name, latitude, longitude, description FROM locations";
$result = $conn->query($sql);

$locations = array();

if ($result->num_rows > 0) {
    // 取出每一行資料
    while($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
} else {
    echo "0 results";
}

// 返回 JSON 格式的資料
echo json_encode($locations);

// 關閉資料庫連接
$conn->close();
?>
