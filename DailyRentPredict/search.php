<?php
header('Content-Type: application/json'); // 設定回應格式為 JSON

// 取得 POST 傳來的資料
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

// 驗證輸入
if (!isset($data['userInput']) || empty($data['userInput'])) {
    echo json_encode(['success' => false, 'error' => '未收到有效的輸入內容']);
    exit;
}

$stationName = $data['stationName'];

try {
    // 資料庫連線設定
    $host = 'localhost';
    $dbname = 'final'; // 替換為你的資料庫名稱
    $username = 'root';        // 替換為你的資料庫使用者名稱
    $password = 'MySQLpassword';            // 替換為你的資料庫密碼

    // 建立 PDO 連線
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 查詢資料庫是否有匹配的輸入
    $stmt = $conn->prepare("SELECT * FROM station_list WHERE station_name = :stationName");
    $stmt->bindParam(':stationName', $stationName);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        // 如果資料不存在
        echo json_encode(['success' => false]);
    } else {
        echo json_encode(['success' => true]);
    }
} catch (PDOException $e) {
    // 資料庫連線或查詢錯誤
    echo json_encode(['success' => false, 'error' => '資料庫錯誤：' . $e->getMessage()]);
} catch (Exception $e) {
    // 其他伺服器錯誤
    echo json_encode(['success' => false, 'error' => '伺服器錯誤：' . $e->getMessage()]);
}
