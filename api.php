<?php
include 'config.php';
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) exit;

$ip = $data['ip'];
$uid = $data['uid'];
$srv = $data['srv'];
$action = $data['action'];

// --- LOGIC GỬI THÔNG BÁO TELEGRAM ---
$msg = "🚀 **HỆ THỐNG SOLITUDE THÔNG BÁO**\n";
$msg .= "━━━━━━━━━━━━━━━\n";
$msg .= "📡 Hành động: `$action`\n";
$msg .= "🆔 UID: `$uid`\n";
$msg .= "🌐 IP: `$ip`\n";
$msg .= "🖥️ Server: `" . strtoupper($srv) . "`\n";
$msg .= "⏰ Thời gian: " . date("H:i:s d/m/Y");

// Nút bấm điều khiển trực tiếp trên Telegram
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '✅ AUTO ADD (30 NGÀY)', 'callback_data' => "add_30_$uid"],
            ['text' => '✅ ADD (VĨNH VIỄN)', 'callback_data' => "add_999_$uid"]
        ],
        [
            ['text' => '🚫 BAN IP MÁY', 'callback_data' => "ban_$ip"],
            ['text' => '🔓 GỠ BAN', 'callback_data' => "unban_$ip"]
        ]
    ]
];

$url = "https://api.telegram.org/bot".TELE_TOKEN."/sendMessage?chat_id=".CHAT_ID."&text=".urlencode($msg)."&parse_mode=Markdown&reply_markup=".json_encode($keyboard);
file_get_contents($url);

// --- LOGIC AUTO BAN (Ví dụ check mượn UID) ---
// Nếu bạn muốn check IP cũ và mới, bạn cần đọc file antena.json tại đây. 
// Nếu IP không khớp -> Tự động gửi lệnh Ban về Tele.
?>
