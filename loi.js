const uid_list_url = "https://raw.githubusercontent.com/maihoangquy2003-wq/ff-neon/main/antena.json";

async function solitudeCheck() {
    let url = $request.url;
    let match = url.match(/uid=(\d+)/);

    if (!match) {
        $done({
            response: {
                status: 403,
                headers: { "Content-Type": "text/plain" },
                body: "SOLITUDE SECURITY: NO UID DETECTED"
            }
        });
        return;
    }

    let currentUID = match[1];

    // 2. Tải danh sách UID từ GitHub
    $httpClient.get(uid_list_url, function(error, response, data) {
        if (error) {
            // Nếu lỗi mạng, cho qua để tránh kẹt game hoặc chặn tùy bạn
            $done({});
            return;
        }

        try {
            let obj = JSON.parse(data);
            let user = obj.users ? obj.users[currentUID] : null;
            let now = new Date();

            // 3. Kiểm tra sự tồn tại của UID
            if (!user) {
                $done({
                    response: {
                        status: 403,
                        body: "SOLITUDE: UID " + currentUID + " IS NOT AUTHORIZED"
                    }
                });
            } 
            // 4. Kiểm tra hạn dùng (LIFETIME hoặc Ngày cụ thể)
            else if (user.expiry && user.expiry !== "Vĩnh viễn" && user.expiry !== "LIFETIME") {
                let expDate = new Date(user.expiry);
                if (now > expDate) {
                    $done({
                        response: {
                            status: 403,
                            body: "SOLITUDE: ACCESS EXPIRED ON " + user.expiry
                        }
                    });
                } else {
                    $done({}); // Hợp lệ -> Cho vào game
                }
            } 
            else {
                $done({}); // Trường hợp Vĩnh viễn -> Cho vào game
            }

        } catch (e) {
            $done({ response: { status: 403, body: "SOLITUDE: DATABASE ERROR" } });
        }
    });
}

solitudeCheck();
p