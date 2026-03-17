let url = $request.url;
let body = $response.body;

if (url.includes("freefiremobile.com")) {
    try {
        let obj = JSON.parse(body);
        let playerUID = obj.account_id || obj.uid || "Người dùng VIP";

        // Hiện thông báo chào mừng ngay trên màn hình game
        $notification.post("🚀 HQUY NEON", "Kích hoạt thành công", "Chào mừng: " + playerUID);
        
    } catch (e) {}
}

$done({ body });
