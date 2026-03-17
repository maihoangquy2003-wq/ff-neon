#/*
 * SOLITUDE AUTO-ID EXTRACTOR
 * Tự động săn UID từ Access Token và Header
 */

const url = $request.url;
const header = JSON.stringify($request.headers);
const body = $request.body;

// Hàm tìm dãy số UID (thường 10 chữ số)
function findUID(text) {
    if (!text) return null;
    let match = text.match(/\b\d{10}\b/g); // Tìm số có 10 chữ số
    return match ? match[0] : null;
}

let uid = findUID(url) || findUID(body) || findUID(header);

if (uid || url.indexOf("access_token") !== -1) {
    // Nếu tìm thấy số giống UID hoặc thấy Token đi qua
    $notification.post("🎯 SOLITUDE CATCHER", "Phát hiện gói tin quan trọng!", "UID dự đoán: " + (uid || "Đang lấy từ Token..."));
    
    // Log chi tiết vào tab Data để bạn copy một lần là xong
    console.log("---------- [SOLITUDE DATA] ----------");
    console.log("URL: " + url);
    if (uid) console.log("FOUND UID: " + uid);
    console.log("TOKEN: " + (url.split('access_token=')[1] || "Không có"));
    console.log("-------------------------------------");
}

$done({});
