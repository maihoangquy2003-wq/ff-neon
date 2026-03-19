<?php
// --- GITHUB CONFIG ---
define('GH_TOKEN', 'GẮN_TOKEN_GITHUB_TẠI_ĐÂY');
define('GH_REPO', 'maihoangquy2003-wq/ff-neon');
define('GH_FILE', 'antena.json');

// --- TELEGRAM CONFIG ---
define('TELE_TOKEN', 'GẮN_TOKEN_BOT_TELE_TẠI_ĐÂY');
define('CHAT_ID', 'GẮN_ID_CHAT_CỦA_BẠN');
define('GROUP_SUPPORT', 'https://t.me/your_group');

// --- LINK TẢI RIÊNG BIỆT ---
$links = [
    'antena'    => ['cert' => 'https://link-cert-antena.pem', 'cfg' => 'https://link-cfg-antena.conf'],
    'noantena'  => ['cert' => 'https://link-cert-noantena.pem', 'cfg' => 'https://link-cfg-noantena.conf'],
    'free'      => ['cert' => 'https://link-cert-free.pem', 'cfg' => 'https://link-cfg-free.conf']
];

// --- TRẠNG THÁI SERVER FREE ---
$server_free_status = "OPEN"; // Đổi thành "CLOSED" để đóng
?>
