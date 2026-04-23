<?php
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    // Giả lập kiểm tra tài khoản
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Sau này thay bằng SQLite
    if ($username === 'admin' && $password === '123456') {
        echo json_encode(['success' => true, 'token' => 'abc123']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sai tài khoản hoặc mật khẩu']);
    }
} elseif ($action === 'activate') {
    $key = $_POST['key'] ?? '';
    // Xử lý kích hoạt key, kiểm tra IP, thời hạn...
    echo json_encode(['success' => true, 'message' => "Key $key đã kích hoạt"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
}
?>