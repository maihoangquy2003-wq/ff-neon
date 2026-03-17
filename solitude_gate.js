const url = $request.url;
const githubUrl = "https://raw.githubusercontent.com/maihoangquy2003-wq/ff-neon/main/antena.json";

// Nếu là gói tin msdk hoặc connect
if (url.indexOf("msdk") !== -1 || url.indexOf("connect") !== -1) {
    $httpClient.get(githubUrl, function(error, response, data) {
        let users = JSON.parse(data).users;
        let myID = "8630164412"; // ID này sau này sẽ lấy tự động từ Body

        if (!users[myID]) {
            // NẾU KHÔNG CÓ ID -> TRẢ VỀ LỖI 403 VÀ HIỆN BẢNG CHẶN
            $notification.post("🛑 HỆ THỐNG SOLITUDE", "TRUY CẬP BỊ TỪ CHỐI", "ID " + myID + " chưa được kích hoạt!");
            $done({ status: "HTTP/1.1 403 Forbidden" }); 
        } else {
            // CÓ ID -> CHO QUA PROXY VPS CỦA BẠN
            $done({ address: "85.31.54.36", port: 8080 });
        }
    });
} else {
    $done({});
}
