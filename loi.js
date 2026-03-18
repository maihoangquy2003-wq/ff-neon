/*
 * SOLITUDE SYSTEM - KEY LOGIN (IP WHITELIST)
 */

const database_url = "https://raw.githubusercontent.com/maihoangquy2003-wq/ff-neon/main/antena.json";

async function solitudeAuth() {
    const user_ip = $request.address; // Lấy IP của thiết bị đang dùng Proxy

    $httpClient.get(database_url, function(error, response, data) {
        if (error) { $done({}); return; }

        try {
            const db = JSON.parse(data);
            const users = db.users;
            let isAllowed = false;
            let reason = "UID NOT FOUND OR IP NOT REGISTERED";

            // Duyệt qua danh sách User để tìm IP khớp
            for (let id in users) {
                if (users[id].ip === user_ip) {
                    // Kiểm tra hạn dùng
                    const expiry = users[id].expiry;
                    if (expiry === "Vĩnh viễn" || expiry === "LIFETIME") {
                        isAllowed = true;
                    } else {
                        const expDate = new Date(expiry);
                        if (new Date() < expDate) {
                            isAllowed = true;
                        } else {
                            reason = "ACCESS EXPIRED FOR UID: " + id;
                        }
                    }
                    break; 
                }
            }

            if (isAllowed) {
                $done({}); // IP hợp lệ -> Vào game
            } else {
                $done({
                    response: {
                        status: 403,
                        headers: { "Content-Type": "text/plain" },
                        body: "SOLITUDE SECURITY: " + reason
                    }
                });
            }

        } catch (e) {
            $done({ response: { status: 403, body: "SOLITUDE: DATABASE ERROR" } });
        }
    });
}

solitudeAuth();