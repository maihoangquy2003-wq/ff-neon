/*
 * SOLITUDE SYSTEM - KEY LOGIN (IP WHITELIST)
 * Script check IP để chặn 403
 */

const database_url = "https://raw.githubusercontent.com/maihoangquy2003-wq/ff-neon/main/antena.json";

async function solitudeAuth() {
    const user_ip = $request.address; // Lấy IP máy đang truy cập

    $httpClient.get(database_url, function(error, response, data) {
        if (error) { 
            $done({}); // Nếu lỗi mạng GitHub thì cho qua để tránh kẹt game
            return; 
        }

        try {
            const db = JSON.parse(data);
            const users = db.users;
            let isAllowed = false;
            let reason = "IP " + user_ip + " CHƯA KÍCH HOẠT";

            // Duyệt danh sách tìm IP khớp
            for (let id in users) {
                if (users[id].ip === user_ip) {
                    const expiry = users[id].expiry;
                    // Kiểm tra hạn dùng
                    if (expiry === "Vĩnh viễn" || expiry === "LIFETIME") {
                        isAllowed = true;
                    } else {
                        const expDate = new Date(expiry);
                        if (new Date() < expDate) {
                            isAllowed = true;
                        } else {
                            reason = "ID " + id + " DÃ HẾT HẠN";
                        }
                    }
                    break; 
                }
            }

            if (isAllowed) {
                $done({}); // OK cho vào game
            } else {
                // Trả về lỗi 403 kèm thông báo
                $done({
                    response: {
                        status: 403,
                        headers: { "Content-Type": "text/plain; charset=utf-8" },
                        body: "SOLITUDE SECURITY: " + reason
                    }
                });
            }
        } catch (e) {
            $done({ response: { status: 403, body: "DATABASE ERROR" } });
        }
    });
}

solitudeAuth();
