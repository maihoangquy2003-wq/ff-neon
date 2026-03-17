/**
 * Script: Check UID & Tele Report
 * Tác dụng: Tự động lấy UID Free Fire, check whitelist, báo về Tele.
 */

const BOT_TOKEN = "8626643586:AAET1UUTKaGzDCit47o3UMAYKHdyGQLKSN0";
const CHAT_ID = "7329868082";
const WHITELIST_URL = "https://raw.githubusercontent.com/maihoangquy2003-wq/ff-neon/main/antena.json";

let url = $request.url;
let body = $response.body;

// Tìm UID trong gói tin login của Free Fire
if (url.includes("freefiremobile.com") && body) {
    try {
        let obj = JSON.parse(body);
        // Tùy theo phiên bản, UID có thể là account_id hoặc uid
        let playerUID = obj.account_id || obj.uid || (obj.data ? obj.data.uid : null);

        if (playerUID) {
            // 1. Gửi IP và UID về Telegram
            let ip = $request.headers['Remote-Addr'] || "N/A";
            sendToTele(`👤 **PLAYER CONNECT**\nID: \`${playerUID}\`\nIP: \`${ip}\``);

            // 2. Kiểm tra UID với Server JSON
            $httpClient.get(WHITELIST_URL, function(error, response, data) {
                if (!error && data.includes(playerUID)) {
                    $notification.post("✅ VIP ACTIVE", "UID: " + playerUID, "Đã xác thực. Chúc bạn chơi game vui vẻ!");
                    $done({ body }); // Cho phép vào game
                } else {
                    $notification.post("❌ CẢNH BÁO", "ID: " + playerUID + " chưa đăng ký!", "Bấm vào để mua quyền truy cập.", {"url": "https://zalo.me/sdt_cua_ban"});
                    // Chặn gói tin, hiện lỗi 403 không cho vào game
                    $done({ status: "HTTP/1.1 403 Forbidden", body: "{}" });
                }
            });
            return;
        }
    } catch (e) {
        $done({ body });
    }
} else {
    $done({ body });
}

function sendToTele(msg) {
    const teleUrl = `https://api.telegram.org/bot${BOT_TOKEN}/sendMessage`;
    $httpClient.post({
        url: teleUrl,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ "chat_id": CHAT_ID, "text": msg, "parse_mode": "Markdown" })
    }, () => {});
}
