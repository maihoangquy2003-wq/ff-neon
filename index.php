<?php
session_start();

// --------------------- BẢO MẬT HEADER NÂNG CAO ---------------------
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()");

$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';");

// --------------------- CẤU HÌNH DATABASE RIÊNG BIỆT FREE/VIP ---------------------
$dbs = [
    'noantena_free' => 'db_noantena_free.json',
    'noantena_vip'  => 'db_noantena_vip.json',
    'antena_vip'    => 'db_antena_vip.json'
];

$ip_files = [
    'noantena_free' => 'ips_noantena_free.txt',
    'noantena_vip'  => 'ips_nex.txt',
    'antena_vip'    => 'ips_antena_vip.txt'
];

foreach ($dbs as $file) {
    if (!file_exists($file)) file_put_contents($file, json_encode([]));
}

$accounts_file = 'accounts.json';
if (!file_exists($accounts_file)) file_put_contents($accounts_file, json_encode([]));

// --------------------- HÀM ĐỒNG BỘ ---------------------
function syncAllServers($dbs, $ip_files) {
    $stopped = (file_exists('server_status.json')) ? json_decode(file_get_contents('server_status.json'), true)['stopped'] : false;
    foreach ($dbs as $sv_key => $db_path) {
        if (!file_exists($db_path)) continue;
        $db_data = json_decode(file_get_contents($db_path), true) ?? [];
        $active_ips = []; $has_change = false;
        foreach ($db_data as $key_id => $info) {
            $frozen = $info['frozen'] ?? false;
            if (!$stopped && !$frozen) {
                if (isset($info['expires_at']) && $info['expires_at'] !== null && time() > $info['expires_at']) {
                    unset($db_data[$key_id]); $has_change = true; continue;
                }
            }
            if (!empty($info['ips'])) foreach ($info['ips'] as $ip) $active_ips[] = $ip;
        }
        if ($has_change) file_put_contents($db_path, json_encode($db_data));
        if (isset($ip_files[$sv_key])) {
            $unique_ips = array_unique($active_ips);
            file_put_contents($ip_files[$sv_key], implode("\n", $unique_ips));
        }
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verifyCsrfToken($token) { return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token); }
function rateLimitCheck($action, $max = 5, $window = 60) {
    $ip = $_SERVER['REMOTE_ADDR']; $key = "rate_{$action}_{$ip}"; $now = time();
    if (!isset($_SESSION[$key])) { $_SESSION[$key] = ['count' => 1, 'start' => $now]; return true; }
    $data = $_SESSION[$key];
    if ($now - $data['start'] > $window) { $_SESSION[$key] = ['count' => 1, 'start' => $now]; return true; }
    if ($data['count'] >= $max) return false;
    $_SESSION[$key]['count']++; return true;
}
function getRealIpAddr() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']); $ip = trim($ips[0]); }
    elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'Unknown';
}
function checkLoginSession() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) return false;
    if (!isset($_SESSION['login_ip']) || $_SESSION['login_ip'] !== getRealIpAddr()) return false;
    if (!isset($_SESSION['ua']) || $_SESSION['ua'] !== $_SERVER['HTTP_USER_AGENT']) return false;
    if (!isset($_SESSION['login_time']) || time() - $_SESSION['login_time'] > 86400) return false;
    return true;
}

$is_logged_in = checkLoginSession();
$user_ip = getRealIpAddr();
$account_type = $_SESSION['account_type'] ?? 'free';

// --------------------- XỬ LÝ POST ---------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($input)) parse_str(file_get_contents('php://input'), $input);
    if (!verifyCsrfToken($input['csrf_token'] ?? $_POST['csrf_token'] ?? '')) {
        http_response_code(403); echo json_encode(['status' => 'error', 'msg' => 'CSRF token không hợp lệ.']); exit;
    }
    if (!rateLimitCheck('api', 8, 60)) {
        http_response_code(429); echo json_encode(['status' => 'error', 'msg' => 'Quá nhiều yêu cầu.']); exit;
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $username = trim($_POST['username'] ?? ''); $password = trim($_POST['password'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_-]{3,32}$/', $username)) {
            echo json_encode(['status' => 'error', 'msg' => 'Tên đăng nhập không hợp lệ.']); exit;
        }
        $accounts = json_decode(file_get_contents($accounts_file), true) ?? [];
        if (!isset($accounts[$username])) { sleep(1); echo json_encode(['status' => 'error', 'msg' => 'Tài khoản hoặc mật khẩu không đúng.']); exit; }
        $acc = $accounts[$username];
        if (isset($acc['expires_at']) && time() > $acc['expires_at']) { echo json_encode(['status' => 'error', 'msg' => 'Tài khoản đã hết hạn.']); exit; }
        if (!password_verify($password, $acc['password'])) { sleep(1); echo json_encode(['status' => 'error', 'msg' => 'Tài khoản hoặc mật khẩu không đúng.']); exit; }
        $max_devices = $acc['max_devices'] ?? 1; $login_ips = $acc['login_ips'] ?? []; $unique_ips = array_unique($login_ips);
        if (!in_array($user_ip, $unique_ips) && count($unique_ips) >= $max_devices) {
            echo json_encode(['status' => 'error', 'msg' => "Tài khoản đã đạt giới hạn {$max_devices} thiết bị."]); exit;
        }
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true; $_SESSION['username'] = $username; $_SESSION['account_type'] = $acc['type'] ?? 'free';
        $_SESSION['login_ip'] = $user_ip; $_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT']; $_SESSION['login_time'] = time();
        if (!in_array($user_ip, $acc['login_ips'] ?? [])) {
            $accounts[$username]['login_ips'][] = $user_ip;
            if (count($accounts[$username]['login_ips']) > 20) array_shift($accounts[$username]['login_ips']);
        }
        $accounts[$username]['last_login'] = time(); $accounts[$username]['total_logins'] = ($acc['total_logins'] ?? 0) + 1;
        file_put_contents($accounts_file, json_encode($accounts));
        echo json_encode(['status' => 'success', 'msg' => 'Đăng nhập thành công!', 'account_type' => $acc['type'] ?? 'free', 'username' => $username]); exit;
    }

    if ($action === 'activate_key') {
        $input_key = trim($_POST['key'] ?? ''); $server_choice = trim($_POST['server'] ?? ''); $key_type = $_POST['key_type'] ?? $_SESSION['account_type'] ?? 'free';
        if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/', $input_key)) { echo json_encode(['status' => 'error', 'msg' => 'Định dạng key không hợp lệ.']); exit; }
        $server_status = file_exists('server_status.json') ? json_decode(file_get_contents('server_status.json'), true) : ['stopped' => false];
        if ($server_status['stopped']) { echo json_encode(['status' => 'error', 'msg' => 'Server hiện đã dừng hoạt động.']); exit; }
        $db_key = $server_choice . '_' . $key_type;
        if (!isset($dbs[$db_key])) { echo json_encode(['status' => 'error', 'msg' => 'Server không hợp lệ.']); exit; }
        $db_file = $dbs[$db_key]; $f_handle = fopen($db_file, 'c+');
        if (!$f_handle || !flock($f_handle, LOCK_EX)) { echo json_encode(['status' => 'error', 'msg' => 'Không thể khóa file dữ liệu.']); exit; }
        $content = stream_get_contents($f_handle); $db = json_decode($content, true) ?? [];
        if (!isset($db[$input_key])) { flock($f_handle, LOCK_UN); fclose($f_handle); echo json_encode(['status' => 'error', 'msg' => 'Key không tồn tại.']); exit; }
        $current_key = $db[$input_key];
        if (!empty($current_key['frozen'])) { flock($f_handle, LOCK_UN); fclose($f_handle); echo json_encode(['status' => 'error', 'msg' => 'Key đang bị đóng băng.']); exit; }
        if ($current_key['expires_at'] == null) $current_key['expires_at'] = time() + ($current_key['duration'] * 3600);
        if (time() > $current_key['expires_at']) { flock($f_handle, LOCK_UN); fclose($f_handle); echo json_encode(['status' => 'error', 'msg' => 'Key đã hết hạn.']); exit; }
        if (!in_array($user_ip, $current_key['ips'])) {
            if (count($current_key['ips']) >= $current_key['max_ips']) { flock($f_handle, LOCK_UN); fclose($f_handle); echo json_encode(['status' => 'error', 'msg' => 'Key đã đạt giới hạn IP.']); exit; }
            $current_key['ips'][] = $user_ip; $current_key['uses'] += 1;
        }
        $db[$input_key] = $current_key; ftruncate($f_handle, 0); rewind($f_handle);
        fwrite($f_handle, json_encode($db)); flock($f_handle, LOCK_UN); fclose($f_handle);
        syncAllServers($dbs, $ip_files);
        echo json_encode(['status' => 'success', 'owner' => htmlspecialchars($current_key['owner'], ENT_QUOTES, 'UTF-8'), 'ip' => $user_ip, 'expires_at' => $current_key['expires_at'], 'key_type' => $current_key['type'] ?? 'free']); exit;
    }

    if ($action === 'preview_key') {
        $input_key = trim($_POST['key'] ?? ''); $server_choice = trim($_POST['server'] ?? ''); $key_type = $_POST['key_type'] ?? $_SESSION['account_type'] ?? 'free';
        if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/', $input_key)) { echo json_encode(['status' => 'error', 'msg' => 'Định dạng key không hợp lệ.']); exit; }
        $db_key = $server_choice . '_' . $key_type;
        if (!isset($dbs[$db_key])) { echo json_encode(['status' => 'error', 'msg' => 'Server không hợp lệ.']); exit; }
        $db_file = $dbs[$db_key];
        if (!file_exists($db_file)) { echo json_encode(['status' => 'error', 'msg' => 'Cơ sở dữ liệu không tồn tại.']); exit; }
        $db = json_decode(file_get_contents($db_file), true) ?? [];
        if (!isset($db[$input_key])) { echo json_encode(['status' => 'error', 'msg' => 'Key không tồn tại.']); exit; }
        $key_data = $db[$input_key];
        if (!empty($key_data['frozen'])) { echo json_encode(['status' => 'error', 'msg' => 'Key đang bị đóng băng.']); exit; }
        if (isset($key_data['expires_at']) && $key_data['expires_at'] !== null && time() > $key_data['expires_at']) { echo json_encode(['status' => 'error', 'msg' => 'Key đã hết hạn.']); exit; }
        $remaining = isset($key_data['expires_at']) ? $key_data['expires_at'] - time() : $key_data['duration'] * 3600;
        $h = floor($remaining / 3600); $m = floor(($remaining % 3600) / 60); $s = $remaining % 60;
        echo json_encode(['status' => 'success', 'owner' => htmlspecialchars($key_data['owner'] ?? 'Vô Danh', ENT_QUOTES, 'UTF-8'), 'server' => strtoupper(str_replace('_', ' ', $server_choice)), 'duration' => $key_data['duration'] . " Giờ", 'remaining' => sprintf("%02d:%02d:%02d", $h, $m, $s), 'ip' => $user_ip, 'key_type' => $key_data['type'] ?? 'free']); exit;
    }

    if ($action === 'logout') { session_destroy(); echo json_encode(['status' => 'success', 'msg' => 'Đã đăng xuất.']); exit; }
    echo json_encode(['status' => 'error', 'msg' => 'Hành động không hợp lệ.']); exit;
}

$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>⚡ SOLITUDE · NEON GATE</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style nonce="<?= $nonce ?>">
        /* ========== RESET & BIẾN MỚI – GỌN GÀNG HIỆN ĐẠI ========== */
        *{margin:0;padding:0;box-sizing:border-box}
        :root{
            --neon-cyan: #00f3ff;
            --neon-magenta: #ff00ff;
            --neon-gold: #ffdd00;
            --neon-blue: #0066ff;
            --neon-purple: #aa00ff;
            --neon-green: #00ff88;
            --glass-dark: rgba(2, 8, 20, 0.75);
            --glass-light: rgba(10, 25, 45, 0.6);
        }
        body{
            background: #000;
            font-family: 'Rajdhani', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 12px;
            color: #fff;
        }
        body::before{
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 30% 40%, #0a1a2f 0%, #020408 100%);
            z-index: -3;
        }
        body::after{
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                linear-gradient(rgba(0, 243, 255, 0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 243, 255, 0.08) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -2;
            opacity: 0.5;
            pointer-events: none;
        }
        #starfield-canvas{
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        /* ========== CONTAINER CHÍNH – CỰC KỲ MƯỢT ========== */
        .main-container{
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 560px;
            backdrop-filter: blur(30px) saturate(200%);
            background: rgba(2, 10, 24, 0.7);
            border-radius: 48px;
            padding: 28px 22px 32px;
            border: 1.5px solid rgba(0, 243, 255, 0.3);
            box-shadow: 0 20px 50px rgba(0,0,0,0.7), 0 0 40px rgba(0, 243, 255, 0.3), 0 0 80px rgba(255, 0, 255, 0.2);
            animation: floatIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            transition: box-shadow 0.3s;
        }
        .main-container:hover{
            box-shadow: 0 20px 50px #000, 0 0 60px rgba(0, 243, 255, 0.5), 0 0 100px rgba(255, 0, 255, 0.3);
        }
        @keyframes floatIn {
            0% { opacity: 0; transform: translateY(40px) scale(0.92); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ========== HEADER ========== */
        .server-banner{ text-align: center; margin-bottom: 20px; }
        .server-banner h1{
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            font-size: 44px;
            background: linear-gradient(135deg, #fff, #a0f0ff, #ffb0ff, #ffffa0);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: 12px;
            text-transform: uppercase;
            text-shadow: 0 0 30px cyan, 0 0 60px magenta;
            margin-bottom: 4px;
        }
        .server-banner .server-sub{
            font-family: 'Orbitron';
            font-size: 14px;
            font-weight: 700;
            color: var(--neon-gold);
            text-shadow: 0 0 20px gold;
            letter-spacing: 8px;
            margin-bottom: 14px;
        }
        .divider{
            width: 80%;
            height: 3px;
            margin: 14px auto;
            background: linear-gradient(90deg, transparent, var(--neon-cyan), var(--neon-magenta), transparent);
            border-radius: 3px;
        }

        /* ========== IP & LOGIN NOTICE ========== */
        .ip-display{
            background: rgba(0,0,0,0.6);
            border-radius: 40px;
            padding: 14px 22px;
            margin: 16px 0;
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 6px solid var(--neon-gold);
            box-shadow: 0 0 20px rgba(255, 221, 0, 0.4);
            backdrop-filter: blur(12px);
        }
        .ip-display i{ font-size: 22px; color: var(--neon-gold); }
        .ip-text{ font-family: 'Orbitron'; font-size: 18px; font-weight: 800; color: #fff; text-shadow: 0 0 12px cyan; }
        .ip-label-small{ font-size: 11px; color: var(--neon-gold); margin-left: auto; font-weight: 700; letter-spacing: 2px; }

        .login-notice{
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(20px);
            border-radius: 40px;
            padding: 24px 20px;
            margin-bottom: 20px;
            border: 2px solid rgba(255, 221, 0, 0.6);
            box-shadow: 0 0 25px gold;
            text-align: center;
        }
        .login-notice i{ font-size: 36px; color: var(--neon-gold); margin-bottom: 8px; }
        .login-notice h2{ font-family: 'Orbitron'; font-size: 18px; font-weight: 800; letter-spacing: 4px; }

        /* ========== GROUP & BUTTONS ========== */
        .group-join-section{
            background: rgba(0,0,0,0.65);
            border-radius: 40px;
            padding: 22px 18px;
            margin: 20px 0;
            border: 1.5px solid rgba(0, 243, 255, 0.5);
            backdrop-filter: blur(20px);
            box-shadow: 0 0 30px rgba(0, 243, 255, 0.3);
        }
        .group-title{
            text-align: center;
            font-family: 'Orbitron';
            font-weight: 900;
            font-size: 15px;
            color: var(--neon-gold);
            margin-bottom: 16px;
            letter-spacing: 4px;
        }
        .group-buttons{ display: flex; gap: 14px; }
        .group-btn{
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 8px;
            border-radius: 50px;
            font-family: 'Orbitron';
            font-weight: 800;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 14px;
            border: 2px solid #fff;
            transition: 0.25s;
        }
        .zalo-btn{ background: linear-gradient(135deg, #0066cc, #0099ff); color: #fff; }
        .tele-btn{ background: linear-gradient(135deg, #1e90ff, #0088cc); color: #fff; }
        .group-btn:hover{ transform: translateY(-4px); filter: brightness(1.2); box-shadow: 0 0 30px currentColor; }

        .support-purchase-section{ display: flex; gap: 16px; margin: 20px 0; }
        .support-btn, .purchase-btn{
            flex: 1;
            padding: 16px 10px;
            border-radius: 50px;
            font-family: 'Orbitron';
            font-weight: 800;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 3px;
            border: 2px solid #fff;
            cursor: pointer;
            transition: 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(10px);
            color: #fff;
        }
        .support-btn{ background: linear-gradient(135deg, #1e90ff, #0088cc); }
        .purchase-btn{ background: linear-gradient(135deg, #aa00ff, #ff00ff); }
        .support-btn:hover, .purchase-btn:hover{ transform: translateY(-4px); filter: brightness(1.2); box-shadow: 0 0 35px currentColor; }

        .login-btn-large{
            width: 100%;
            padding: 22px 20px;
            background: linear-gradient(95deg, #0cf, #0066ff);
            border: none;
            border-radius: 60px;
            font-family: 'Orbitron';
            font-weight: 900;
            font-size: 22px;
            color: #000;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 0 40px cyan, 0 10px 20px rgba(0,0,0,0.5);
            margin-top: 12px;
            letter-spacing: 6px;
            text-transform: uppercase;
            border: 3px solid #fff;
        }

        /* ========== USER BAR ========== */
        .user-status-bar{
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(15px);
            border-radius: 45px;
            padding: 12px 20px;
            margin-bottom: 22px;
            border: 1.5px solid rgba(0, 243, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .user-avatar{
            width: 46px;
            height: 46px;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-purple));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 0 25px cyan;
        }
        .user-name{ font-family: 'Orbitron'; font-weight: 800; font-size: 16px; }
        .user-badge{ font-size: 10px; padding: 4px 12px; border-radius: 25px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; display: inline-block; margin-top: 4px; }
        .badge-vip{ background: linear-gradient(135deg, #ffd700, #ff8c00); color: #000; }
        .badge-free{ background: linear-gradient(135deg, #0cf, #0a6); color: #000; }
        .logout-btn-small{
            background: transparent;
            border: 2px solid var(--neon-cyan);
            color: var(--neon-cyan);
            padding: 10px 18px;
            border-radius: 35px;
            font-family: 'Orbitron';
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: 0.25s;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* ========== SERVER BUTTONS & INPUT ========== */
        .server-grid{ display: flex; gap: 15px; margin-bottom: 24px; }
        .btn-sv{
            flex: 1;
            background: rgba(10,20,35,0.8);
            border: 2px solid rgba(0,243,255,0.5);
            padding: 16px 6px;
            border-radius: 50px;
            font-family: 'Orbitron';
            font-weight: 700;
            font-size: 14px;
            color: #e0f0ff;
            cursor: pointer;
            transition: 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            backdrop-filter: blur(12px);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .btn-sv.active{
            background: rgba(0, 243, 255, 0.25);
            border-color: #fff;
            box-shadow: 0 0 35px cyan;
        }
        .input-group-neon{
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        .input-group-neon input{
            flex: 1;
            padding: 18px 22px;
            background: rgba(0,0,0,0.7);
            border: 2.5px solid var(--neon-cyan);
            border-radius: 60px;
            font-family: 'Orbitron';
            font-size: 16px;
            text-align: center;
            color: #fff;
            letter-spacing: 4px;
            transition: 0.2s;
            outline: none;
            text-transform: uppercase;
        }
        .btn-glow{
            width: 100%;
            padding: 18px;
            background: linear-gradient(95deg, #0cf, #0066ff);
            border: none;
            border-radius: 60px;
            font-family: 'Orbitron';
            font-weight: 900;
            font-size: 18px;
            color: #000;
            cursor: pointer;
            transition: 0.25s;
            box-shadow: 0 0 40px cyan;
            margin-bottom: 15px;
            letter-spacing: 4px;
            text-transform: uppercase;
            border: 3px solid #fff;
        }
        .btn-outline{
            width: 100%;
            padding: 16px;
            background: transparent;
            border: 2.5px solid var(--neon-cyan);
            border-radius: 60px;
            font-family: 'Orbitron';
            font-weight: 700;
            font-size: 15px;
            color: var(--neon-cyan);
            cursor: pointer;
            transition: 0.25s;
            backdrop-filter: blur(10px);
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        /* ========== INFO BOX & SUCCESS ========== */
        .info-box{
            background: rgba(0,0,0,0.65);
            border-radius: 36px;
            padding: 22px;
            margin: 20px 0;
            border: 2px solid rgba(0,243,255,0.7);
            backdrop-filter: blur(20px);
        }
        .info-item{
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .info-item span:first-child{ color: var(--neon-gold); font-weight: 800; text-transform: uppercase; letter-spacing: 2px; }
        .info-item span:last-child{ color: #fff; font-weight: 900; font-family: 'Orbitron'; background: rgba(0,0,0,0.5); padding: 6px 18px; border-radius: 30px; border: 1.5px solid cyan; }

        .success-container{ text-align: center; }
        .success-icon-large{ font-size: 70px; color: #fff; filter: drop-shadow(0 0 30px cyan); margin-bottom: 20px; }
        .success-title-large{ font-family: 'Orbitron'; font-weight: 900; color: #fff; font-size: 32px; text-shadow: 0 0 30px cyan; margin-bottom: 20px; }
        .timer-large{ font-family: 'Orbitron'; font-size: 56px; font-weight: 900; background: linear-gradient(135deg, #fff, #a0f0ff); -webkit-background-clip: text; background-clip: text; color: transparent; margin: 20px 0; }

        /* ========== MODAL – TINH GỌN & SANG TRỌNG ========== */
        .login-modal-overlay, .purchase-modal-overlay{
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9); backdrop-filter: blur(25px); z-index: 1000;
            display: flex; align-items: center; justify-content: center;
        }
        .login-modal{
            background: rgba(5,15,30,0.9); border-radius: 48px; padding: 36px 30px;
            max-width: 420px; width: 90%; border: 2px solid transparent;
            box-shadow: 0 0 80px cyan, 0 0 150px magenta;
            border-image: linear-gradient(135deg, cyan, magenta) 1;
        }
        .purchase-modal{
            background: rgba(5,15,30,0.95); border-radius: 48px; padding: 30px 25px;
            max-width: 520px; width: 95%; border: 2px solid transparent;
            box-shadow: 0 0 100px cyan, 0 0 200px magenta;
            border-image: linear-gradient(135deg, cyan, magenta) 1;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-title{
            font-family: 'Orbitron'; font-size: 32px; font-weight: 900; text-align: center;
            color: #fff; text-shadow: 0 0 30px cyan; margin-bottom: 30px; letter-spacing: 5px;
        }
        .modal-input, .modal-select{
            width: 100%; padding: 16px 20px; background: rgba(0,0,0,0.7); border: 2.5px solid var(--neon-cyan);
            border-radius: 50px; font-family: 'Orbitron'; font-size: 15px; color: #fff;
            outline: none; margin-bottom: 20px;
        }
        .modal-btn{
            width: 100%; padding: 20px; background: linear-gradient(95deg, #0cf, #0066ff);
            border: none; border-radius: 50px; font-family: 'Orbitron'; font-weight: 900;
            font-size: 20px; color: #000; cursor: pointer; margin-bottom: 16px;
            text-transform: uppercase; letter-spacing: 5px; border: 3px solid #fff;
        }
        .modal-close{
            text-align: center; color: var(--neon-cyan); cursor: pointer;
            font-family: 'Orbitron'; font-weight: 700; text-transform: uppercase;
            letter-spacing: 4px; padding: 12px; font-size: 15px;
        }

        /* === PURCHASE MODAL MỚI – GỌN GÀNG 3 BƯỚC === */
        .price-display{
            background: rgba(0,243,255,0.15); border: 2px solid var(--neon-gold);
            border-radius: 50px; padding: 14px; text-align: center; margin-bottom: 25px;
            font-family: 'Orbitron'; color: var(--neon-gold); font-weight: 900; font-size: 20px;
        }
        .qr-container{ text-align: center; margin: 15px 0; }
        .qr-container img{
            max-width: 200px; border-radius: 24px; border: 3px solid var(--neon-gold);
            box-shadow: 0 0 40px gold;
        }
        .transfer-box{
            background: rgba(0,0,0,0.7); padding: 22px 18px; border-radius: 36px;
            border: 2px solid var(--neon-cyan); margin: 20px 0;
        }
        .transfer-content{
            font-family: 'Orbitron'; font-size: 18px; font-weight: 900; text-align: center;
            word-break: break-all; color: var(--neon-gold); text-shadow: 0 0 15px gold;
            background: #000; padding: 14px; border-radius: 30px; border: 2px solid gold;
            margin-bottom: 15px;
        }
        .transfer-price{
            font-family: 'Orbitron'; font-size: 20px; font-weight: 700; text-align: center;
            color: var(--neon-green); margin-bottom: 20px;
        }
        .copy-btn, .confirm-btn, .back-edit-btn{
            background: var(--neon-gold); color: #000; border: none; padding: 16px 20px;
            border-radius: 50px; font-weight: 900; font-family: 'Orbitron'; cursor: pointer;
            width: 100%; font-size: 17px; letter-spacing: 3px; margin-top: 12px;
            box-shadow: 0 0 25px gold; transition: 0.2s;
        }
        .back-edit-btn{ background: transparent; border: 2.5px solid var(--neon-cyan); color: var(--neon-cyan); }
        .warning-note{
            color: #ff7777; font-size: 14px; text-align: center; margin: 20px 0 10px;
            border-top: 2px dashed var(--neon-gold); padding-top: 18px;
        }

        .hidden{ display: none !important; }
        .swal2-popup{ background: #0a1220!important; border: 3px solid cyan!important; border-radius: 40px!important; color: #fff!important; }
        .swal2-title{ color: cyan!important; }
    </style>
</head>
<body>
<canvas id="starfield-canvas"></canvas>
<div class="main-container">
    <div class="server-banner">
        <h1>SOLITUDE</h1>
        <div class="server-sub">✦ SERVER PREMIUM ✦</div>
        <div class="divider"></div>
    </div>
    <?php if (!$is_logged_in): ?>
        <div class="login-notice"><i class="fas fa-lock"></i><h2>🔐 VUI LÒNG ĐĂNG NHẬP</h2><p>ĐỂ SỬ DỤNG DỊCH VỤ</p></div>
        <div class="ip-display"><i class="fas fa-microchip"></i><span class="ip-text"><?= htmlspecialchars($user_ip) ?></span><span class="ip-label-small">YOUR IP</span></div>
        <div class="group-join-section">
            <div class="group-title"><i class="fas fa-users"></i> THAM GIA NHÓM ĐỂ NHẬN THÔNG BÁO VỀ PROXY MỚI NHẤT </div>
            <div class="group-buttons">
                <a href="https://zalo.me/g/sjp9gecwp85xlicz6pkm" target="_blank" class="group-btn zalo-btn"><i class="fab fa-zalo"></i> ZALO</a>
                <a href="https://t.me/boost/solitudeproxy" target="_blank" class="group-btn tele-btn"><i class="fab fa-telegram"></i> TELEGRAM</a>
            </div>
        </div>
        <div class="support-purchase-section">
            <button class="support-btn" id="supportBtn"><i class="fab fa-telegram"></i> SUPPORT</button>
            <button class="purchase-btn" id="purchaseBtn"><i class="fas fa-shopping-cart"></i> MUA PROXY</button>
        </div>
        <button class="login-btn-large" id="loginBtnGuest"><i class="fas fa-sign-in-alt"></i> ĐĂNG NHẬP NGAY</button>
    <?php else: ?>
        <div class="user-status-bar">
            <div class="user-info"><div class="user-avatar"><i class="fas fa-user-astronaut"></i></div><div class="user-details"><span class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></span><span class="user-badge <?= $account_type === 'vip' ? 'badge-vip' : 'badge-free' ?>"><?= $account_type === 'vip' ? 'VIP' : 'FREE' ?></span></div></div>
            <button class="logout-btn-small" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> LOGOUT</button>
        </div>
        <div class="ip-display"><i class="fas fa-microchip"></i><span class="ip-text"><?= htmlspecialchars($user_ip) ?></span><span class="ip-label-small">YOUR IP</span></div>
        <div class="support-purchase-section">
            <button class="support-btn" id="supportBtnLogged"><i class="fab fa-telegram"></i> SUPPORT</button>
            <button class="purchase-btn" id="purchaseBtnLogged"><i class="fas fa-shopping-cart"></i> MUA PROXY</button>
        </div>
        <div id="step-selection">
            <div class="server-grid">
                <?php if ($account_type === 'vip'): ?><button class="btn-sv active" data-sv="antena"><i class="fas fa-satellite-dish"></i> ANTENNA</button><?php endif; ?>
                <button class="btn-sv <?= $account_type === 'free' ? 'active' : '' ?>" data-sv="noantena"><i class="fas fa-shield-virus"></i> NO ANTENNA</button>
            </div>
            <div class="input-area">
                <div class="input-group-neon"><input type="text" id="keyInp" placeholder="🔑 NHẬP KEY" spellcheck="false" autocomplete="off" maxlength="64"></div>
                <button class="btn-glow" id="previewBtn"><i class="fas fa-search"></i> KIỂM TRA KEY</button>
            </div>
        </div>
        <div id="step-info" class="hidden">
            <div class="info-box">
                <div class="info-item"><span>OWNER</span><span id="info-owner">---</span></div>
                <div class="info-item"><span>SERVER</span><span id="info-sv">---</span></div>
                <div class="info-item"><span>THỜI GIAN</span><span id="info-dur">---</span></div>
                <div class="info-item"><span>CÒN LẠI</span><span id="info-remain">---</span></div>
                <div class="info-item"><span>IP</span><span id="info-ip">---</span></div>
                <div class="info-item"><span>LOẠI</span><span id="info-type">---</span></div>
            </div>
            <button class="btn-glow" id="activateBtn"><i class="fas fa-check-double"></i> KÍCH HOẠT</button>
            <button class="btn-outline" id="backBtn"><i class="fas fa-arrow-left"></i> QUAY LẠI</button>
        </div>
        <div id="step-success" class="hidden">
            <div class="success-container">
                <div class="success-icon-large"><i class="fas fa-check-circle"></i></div>
                <div class="success-title-large">ACCESS GRANTED</div>
                <div class="info-box">
                    <div class="info-item"><span>OWNER</span><span id="res-owner">---</span></div>
                    <div class="info-item"><span>IP</span><span id="res-ip">---</span></div>
                    <div class="info-item"><span>LOẠI</span><span id="res-type">---</span></div>
                </div>
                <div style="margin-top:15px"><i class="far fa-clock"></i> THỜI GIAN CÒN LẠI</div>
                <div class="timer-large" id="timer">00:00:00</div>
            </div>
            <button class="btn-glow" onclick="location.reload()" style="margin-top:20px"><i class="fas fa-sign-out-alt"></i> KẾT THÚC</button>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL ĐĂNG NHẬP -->
<div id="loginModal" class="login-modal-overlay hidden">
    <div class="login-modal">
        <div class="modal-title">🔐 LOGIN</div>
        <form id="loginForm">
            <input type="text" name="username" class="modal-input" placeholder="TÀI KHOẢN" required autocomplete="off">
            <input type="password" name="password" class="modal-input" placeholder="MẬT KHẨU" required autocomplete="off">
            <button type="submit" class="modal-btn">ĐĂNG NHẬP</button>
        </form>
        <div class="modal-close" onclick="closeLoginModal()">✖ ĐÓNG</div>
    </div>
</div>

<!-- MODAL MUA PROXY – THIẾT KẾ LẠI GỌN GÀNG -->
<div id="purchaseModal" class="purchase-modal-overlay hidden">
    <div class="purchase-modal">
        <div class="modal-title">🛒 MUA PROXY</div>
        <div id="purchaseOptions">
            <select id="serverSelect" class="modal-select">
                <option value="noantena">NO ANTENA</option>
                <option value="antena">ANTENA</option>
            </select>
            <select id="durationSelect" class="modal-select">
                <option value="1D">1 NGÀY</option>
                <option value="7D">7 NGÀY</option>
                <option value="30D">1 THÁNG</option>
            </select>
            <select id="deviceSelect" class="modal-select">
                <option value="1">1 THIẾT BỊ</option>
                <option value="10">10 THIẾT BỊ</option>
                <option value="50">50 THIẾT BỊ</option>
                <option value="100">100 THIẾT BỊ</option>
                <option value="500">500 THIẾT BỊ</option>
                <option value="1000">1000 THIẾT BỊ</option>
            </select>
            <div class="price-display" id="priceDisplay">💰 GIÁ: --K</div>
            <button class="modal-btn" id="generateTransferBtn">TẠO NỘI DUNG CK</button>
        </div>
        <div id="transferResult" class="hidden">
            <div class="qr-container">
                <img src="qr.jpg" alt="QR Code" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none; color: var(--neon-gold);">QR không tải được, dùng nội dung bên dưới.</div>
            </div>
            <div class="transfer-box">
                <div class="transfer-content" id="transferContent"></div>
                <div class="transfer-price" id="transferPrice"></div>
                <button class="copy-btn" id="copyTransferBtn"><i class="far fa-copy"></i> COPY NỘI DUNG</button>
                <div class="warning-note">
                    <i class="fas fa-exclamation-triangle"></i> Chuyển khoản chính xác số tiền. Nội dung sai hệ thống không chịu trách nhiệm.
                </div>
            </div>
            <input type="text" id="desiredUsername" class="modal-input" placeholder="TÊN TÀI KHOẢN MONG MUỐN" autocomplete="off">
            <input type="password" id="desiredPassword" class="modal-input" placeholder="MẬT KHẨU MONG MUỐN" autocomplete="off">
            <button class="confirm-btn" id="confirmTransferBtn"><i class="fas fa-check-circle"></i> TÔI ĐÃ CHUYỂN KHOẢN</button>
            <button class="back-edit-btn" id="backEditBtn"><i class="fas fa-edit"></i> CHỌN LẠI</button>
        </div>
        <div class="modal-close" onclick="closePurchaseModal()" style="margin-top: 25px;">✖ ĐÓNG</div>
    </div>
</div>

<script nonce="<?= $nonce ?>">
    // Toàn bộ JavaScript giữ nguyên, chỉ thay đổi CSS và HTML, không ảnh hưởng logic.
    (function(){"use strict";
        const canvas=document.getElementById('starfield-canvas'),ctx=canvas.getContext('2d');let width,height,stars=[],meteors=[];
        function resizeCanvas(){width=window.innerWidth;height=window.innerHeight;canvas.width=width;canvas.height=height}
        window.addEventListener('resize',resizeCanvas);resizeCanvas();
        class Star{constructor(){this.reset()}reset(){this.x=Math.random()*width;this.y=Math.random()*height;this.size=Math.random()*3+1;this.color=`hsl(${Math.random()*60+180},100%,70%)`}update(){}draw(){ctx.beginPath();ctx.arc(this.x,this.y,this.size,0,Math.PI*2);ctx.fillStyle=this.color;ctx.shadowColor='#0ff';ctx.shadowBlur=this.size*8;ctx.fill()}}
        class Meteor{constructor(){this.reset()}reset(){this.x=Math.random()*width;this.y=Math.random()*-200;this.len=Math.random()*120+60;this.speed=Math.random()*12+8;this.thickness=Math.random()*2.5+1.5;this.color=`hsl(${Math.random()*40+180},100%,65%)`}update(){this.x-=this.speed*.6;this.y+=this.speed;if(this.y>height+200||this.x<-200)this.reset()}draw(){ctx.beginPath();ctx.moveTo(this.x,this.y);ctx.lineTo(this.x+this.len*.7,this.y-this.len);ctx.strokeStyle=this.color;ctx.lineWidth=this.thickness;ctx.shadowBlur=25;ctx.shadowColor='#0ff';ctx.stroke()}}
        for(let i=0;i<150;i++)stars.push(new Star());for(let i=0;i<6;i++)meteors.push(new Meteor());
        function animateStars(){ctx.clearRect(0,0,width,height);ctx.shadowBlur=15;stars.forEach(s=>{s.update();s.draw()});meteors.forEach(m=>{m.update();m.draw()});requestAnimationFrame(animateStars)}animateStars();
        const accountType='<?= $account_type ?>';let selectedServer=accountType==='vip'?'antena':'noantena',currentKey='';const csrfToken='<?= $csrf_token ?>',isLoggedIn=<?= $is_logged_in?'true':'false' ?>;
        const loginModal=document.getElementById('loginModal'),loginForm=document.getElementById('loginForm'),stepSelection=document.getElementById('step-selection'),stepInfo=document.getElementById('step-info'),stepSuccess=document.getElementById('step-success'),keyInput=document.getElementById('keyInp'),previewBtn=document.getElementById('previewBtn'),activateBtn=document.getElementById('activateBtn'),backBtn=document.getElementById('backBtn'),logoutBtn=document.getElementById('logoutBtn'),svButtons=document.querySelectorAll('.btn-sv');
        let timerInterval=null;
        function showError(msg){Swal.fire({title:'⚠️ LỖI',text:msg,icon:'error',confirmButtonText:'THỬ LẠI'})}
        function showSuccess(msg){Swal.fire({title:'✅ THÀNH CÔNG',text:msg,icon:'success',confirmButtonText:'OK'})}
        function closeLoginModal(){loginModal.classList.add('hidden')}
        function openLoginModal(){loginModal.classList.remove('hidden')}
        async function doLogin(username,password){const d=new URLSearchParams();d.append('action','login');d.append('username',username);d.append('password',password);d.append('csrf_token',csrfToken);try{const r=await fetch('',{method:'POST',body:d}),data=await r.json();data.status==='success'?(showSuccess('Đăng nhập thành công!'),setTimeout(()=>location.reload(),1000)):showError(data.msg)}catch(e){showError('Lỗi đăng nhập.')}}
        async function doLogout(){const d=new URLSearchParams();d.append('action','logout');d.append('csrf_token',csrfToken);try{await fetch('',{method:'POST',body:d});location.reload()}catch(e){location.reload()}}
        document.getElementById('loginBtnGuest')?.addEventListener('click',openLoginModal);
        loginForm?.addEventListener('submit',e=>{e.preventDefault();const u=loginForm.username.value.trim(),p=loginForm.password.value;if(!u||!p){showError('Vui lòng nhập đầy đủ thông tin.');return}doLogin(u,p)});
        loginModal?.addEventListener('click',e=>{if(e.target===loginModal)closeLoginModal()});
        if(isLoggedIn){svButtons.forEach(b=>{b.addEventListener('click',function(){svButtons.forEach(x=>x.classList.remove('active'));this.classList.add('active');selectedServer=this.dataset.sv})});
            async function previewKey(){const k=keyInput.value.trim();if(!k)return showError('Vui lòng nhập key.');if(!/^[A-Za-z0-9_-]{1,64}$/.test(k))return showError('Định dạng key không hợp lệ.');currentKey=k;const d=new URLSearchParams();d.append('action','preview_key');d.append('key',k);d.append('server',selectedServer);d.append('key_type',accountType);d.append('csrf_token',csrfToken);try{const r=await fetch('',{method:'POST',body:d}),data=await r.json();if(data.status==='success'){document.getElementById('info-owner').textContent=data.owner;document.getElementById('info-sv').textContent=data.server;document.getElementById('info-dur').textContent=data.duration;document.getElementById('info-remain').textContent=data.remaining;document.getElementById('info-ip').textContent=data.ip;document.getElementById('info-type').textContent=(data.key_type||'free').toUpperCase();stepSelection.classList.add('hidden');stepInfo.classList.remove('hidden')}else showError(data.msg)}catch(e){showError('Lỗi kết nối.')}}
            async function activateKey(){const d=new URLSearchParams();d.append('action','activate_key');d.append('key',currentKey);d.append('server',selectedServer);d.append('key_type',accountType);d.append('csrf_token',csrfToken);try{const r=await fetch('',{method:'POST',body:d}),data=await r.json();if(data.status==='success'){document.getElementById('res-owner').textContent=data.owner;document.getElementById('res-ip').textContent=data.ip;document.getElementById('res-type').textContent=(data.key_type||'free').toUpperCase();stepInfo.classList.add('hidden');stepSuccess.classList.remove('hidden');startTimer(data.expires_at)}else showError(data.msg)}catch(e){showError('Lỗi kích hoạt.')}}
            function startTimer(e){if(timerInterval)clearInterval(timerInterval);timerInterval=setInterval(()=>{const r=e-Math.floor(Date.now()/1000);if(r<=0){document.getElementById('timer').textContent='00:00:00';clearInterval(timerInterval);showError('Key đã hết hạn!');setTimeout(()=>location.reload(),2000);return}const h=Math.floor(r/3600),m=Math.floor((r%3600)/60),s=r%60;document.getElementById('timer').textContent=`${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`},1000)}
            previewBtn?.addEventListener('click',previewKey);activateBtn?.addEventListener('click',activateKey);backBtn?.addEventListener('click',()=>{stepInfo.classList.add('hidden');stepSelection.classList.remove('hidden');stepSuccess.classList.add('hidden')});logoutBtn?.addEventListener('click',doLogout);keyInput?.addEventListener('keypress',e=>{if(e.key==='Enter')previewKey()})}
        document.addEventListener('contextmenu',e=>e.preventDefault());
        document.onkeydown=function(e){if(e.keyCode==123||(e.ctrlKey&&e.shiftKey&&(e.keyCode==73||e.keyCode==74))||(e.ctrlKey&&e.keyCode==85))return!1};

        const purchaseModal = document.getElementById('purchaseModal');
        const purchaseOptions = document.getElementById('purchaseOptions');
        const transferResult = document.getElementById('transferResult');
        const transferContent = document.getElementById('transferContent');
        const transferPrice = document.getElementById('transferPrice');
        const priceDisplay = document.getElementById('priceDisplay');
        const serverSelect = document.getElementById('serverSelect');
        const durationSelect = document.getElementById('durationSelect');
        const deviceSelect = document.getElementById('deviceSelect');
        const desiredUsername = document.getElementById('desiredUsername');
        const desiredPassword = document.getElementById('desiredPassword');
        const usedRandoms = new Set();

        const priceTable = {
            noantena: {'1D':{1:15,10:40,50:60,100:80,500:100,1000:150},'7D':{1:70,10:110,50:160,100:200,500:300,1000:400},'30D':{1:180,10:250,50:300,100:400,500:600,1000:800}},
            antena: {'1D':{1:15,10:40,50:60,100:80,500:100,1000:150},'7D':{1:70,10:110,50:160,100:200,500:300,1000:400},'30D':{1:180,10:250,50:300,100:400,500:600,1000:800}}
        };

        function updatePrice(){const s=serverSelect.value,d=durationSelect.value,dev=deviceSelect.value;const p=priceTable[s]?.[d]?.[dev]||'??';priceDisplay.textContent=`💰 GIÁ: ${p}K VND`;}
        serverSelect.addEventListener('change',updatePrice);durationSelect.addEventListener('change',updatePrice);deviceSelect.addEventListener('change',updatePrice);updatePrice();

        function openPurchaseModal(){purchaseModal.classList.remove('hidden');purchaseOptions.classList.remove('hidden');transferResult.classList.add('hidden');}
        function closePurchaseModal(){purchaseModal.classList.add('hidden');}
        document.querySelectorAll('[id^="supportBtn"]').forEach(b=>b.addEventListener('click',()=>window.open('https://t.me/solitude201229','_blank')));
        document.querySelectorAll('[id^="purchaseBtn"]').forEach(b=>b.addEventListener('click',openPurchaseModal));

        document.getElementById('generateTransferBtn').addEventListener('click',function(){
            const s=serverSelect.value,d=durationSelect.value,dev=deviceSelect.value,p=priceTable[s]?.[d]?.[dev]||'??';
            let rand;do{rand=Math.floor(Math.random()*900000)+100000;}while(usedRandoms.has(rand));
            usedRandoms.add(rand);
            transferContent.textContent=`Solitude-${s}-${dev}TB-${d}-${rand}`;
            transferPrice.textContent=`💰 Số tiền: ${p}K VND`;
            purchaseOptions.classList.add('hidden');transferResult.classList.remove('hidden');
        });
        document.getElementById('backEditBtn').addEventListener('click',function(){
            purchaseOptions.classList.remove('hidden');transferResult.classList.add('hidden');
        });
        document.getElementById('copyTransferBtn').addEventListener('click',function(){
            const text=transferContent.textContent;
            navigator.clipboard?.writeText(text).then(()=>{
                Swal.fire({title:'ĐÃ COPY',text:'Nội dung đã sao chép.',icon:'success',timer:1500,showConfirmButton:false});
            }).catch(()=>{
                const ta=document.createElement('textarea');ta.value=text;document.body.appendChild(ta);ta.select();document.execCommand('copy');document.body.removeChild(ta);
                Swal.fire({title:'ĐÃ COPY',text:'Nội dung đã sao chép.',icon:'success',timer:1500,showConfirmButton:false});
            });
        });
        document.getElementById('confirmTransferBtn').addEventListener('click',function(){
            const u=desiredUsername.value.trim(),p=desiredPassword.value.trim();
            if(!u||!p)return Swal.fire({title:'⚠️ THIẾU',text:'Nhập tên và mật khẩu.',icon:'warning'});
            closePurchaseModal();
            Swal.fire({
                title:'🙏 CẢM ƠN',html:`<div style="color:#fff"><i class="fas fa-heart" style="color:#f0f"></i><p>Tài khoản: ${u}<br>MK: ${p}<br>${transferPrice.textContent}<br>CK: ${transferContent.textContent}</p><p>Gửi bill cho admin Telegram.</p></div>`,
                icon:'success',confirmButtonText:'MỞ TELEGRAM'
            }).then(r=>{if(r.isConfirmed)window.open('https://t.me/solitude201229','_blank')});
        });
        purchaseModal.addEventListener('click',e=>{if(e.target===purchaseModal)closePurchaseModal()});
    })();
</script>
</body>
</html>