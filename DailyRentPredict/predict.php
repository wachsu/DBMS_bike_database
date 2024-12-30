<?php
header('Content-Type: application/json'); // 設定回應格式為 JSON

// 取得 POST 傳來的資料
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

// 驗證輸入
if (!isset($data['temp']) || empty($data['temp'])) {
    echo json_encode(['success' => false, 'error' => '未收到有效的輸入內容']);
    exit;
}

$temp = $data['temp'];
$wind = $data['wind'];
$UV = $data['UV'];
$rain = $data['rain'];
$stationName = $data['stationName'];

$output = shell_exec("python model.py " . 
    escapeshellarg($stationName) . " " . 
    escapeshellarg($temp) . " " . 
    escapeshellarg($wind) . " " . 
    escapeshellarg($UV) . " " . 
    escapeshellarg($rain) . " " .
    "2>&1");
if ($output === null) {
    // 處理 Python 腳本失敗
    echo json_encode(['success' => false, 'error' => 'Python 腳本執行失敗']);
} else {
    // 回傳 Python 處理結果
    echo json_encode(['success' => true, 'result' => trim($output)]);
}
