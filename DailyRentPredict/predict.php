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

$temp = $data['temp'];
$wind = $data['wind'];
$UV = $data['UV'];
$rain = $data['rain'];
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

    $output = shell_exec("python model.py " . 
    escapeshellarg($stationName) . " " . 
    escapeshellarg($temp) . " " . 
    escapeshellarg($wind) . " " . 
    escapeshellarg($UV) . " " . 
    escapeshellarg($rain));
    if ($output === null) {
        // 處理 Python 腳本失敗
        echo json_encode(['success' => false, 'error' => 'Python 腳本執行失敗']);
    } else {
        // 回傳 Python 處理結果
        echo json_encode(['success' => true, 'result' => trim($output)]);
    }
} catch (PDOException $e) {
    // 資料庫連線或查詢錯誤
    echo json_encode(['success' => false, 'error' => '資料庫錯誤：' . $e->getMessage()]);
} catch (Exception $e) {
    // 其他伺服器錯誤
    echo json_encode(['success' => false, 'error' => '伺服器錯誤：' . $e->getMessage()]);
}
