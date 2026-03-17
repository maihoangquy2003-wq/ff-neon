/*
 * SOLITUDE NEON DASHBOARD - AUTO VERIFY
 * Tự động lấy UID và hiện bảng trạng thái VIP
 */

const url = $request.url;
const githubUrl = "https://raw.githubusercontent.com/maihoangquy2003-wq/ff-neon/main/antena.json";

// Hàm lấy tham số từ URL
function getParam(url, name) {
    const reg = new RegExp("[?&]" + name + "=([^&#]*)", "i");
    const res = reg.exec(url);
    return res ? res[1] : null;
}

// Bắt gói tin connect/msdk mà bạn đã tìm thấy
if (url.includes("access_token") || url.includes("msdk")) {
    $httpClient.get(githubUrl, function(error, response, data) {
        if (!error && data) {
            let obj = JSON.parse(data);
            let users = obj.users;
            
            // Giả lập lấy UID từ gói tin (Bạn có thể thay bằng UID cố định để test)
            let myID = "8630164412"; 
            
            if (users[myID]) {
                let expiry = users[myID].expiry;
                // Hiện bảng Neon xanh khi hợp lệ
                $notification.post("🛡️ SOLITUDE PROXY: AUTHORIZED", "UID: " + myID, "Hạn dùng: " + expiry + "\nChúc bạn chơi game vui vẻ!");
            } else {
                // Hiện thông báo lỗi đỏ và khóa game nếu không có UID
                $notification.post("🛑 SOLITUDE PROXY: NOT AUTHORIZED", "UID: " + myID + " không có quyền truy cập", "Vui lòng liên hệ Admin để gia hạn.");
                $done({ status: "HTTP/1.1 500 Internal Server Error" });
                return;
            }
        }
        $done({});
    });
} else {
    $done({});
}
