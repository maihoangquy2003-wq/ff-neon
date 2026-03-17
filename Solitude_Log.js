/*
 * Script: Solitude Auto-Sniffer
 * Chức năng: Quét UID từ gói tin Garena/MSDK
 */

const url = $request.url;
const method = $request.method;
const body = $request.body;

if (body) {
    // In dữ liệu ra mục Nhật ký (Data -> Logging)
    console.log("---------- [SOLITUDE LOG] ----------");
    console.log("URL: " + url);
    console.log("Method: " + method);
    console.log("Dữ liệu Body: " + body);
    
    // Nếu phát hiện UID 8630164412 hoặc từ khóa liên quan
    if (body.indexOf("8630164412") !== -1 || body.indexOf("uid") !== -1 || body.indexOf("open_id") !== -1) {
        $notification.post("Solitude Proxy", "🎯 ĐÃ TÌM THẤY UID!", "Nhấn vào Nhật ký để copy nội dung Body.");
    }
}

$done({body});
