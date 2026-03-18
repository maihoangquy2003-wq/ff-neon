/*
Solitude UID Check - Shadowrocket Script
*/

const uid_list_url = "https://raw.githubusercontent.com/maihoangquy2003-wq/ff-neon/main/antena.json";

async function checkUID() {
    // 1. Lấy UID từ yêu cầu của Game (Giả định qua URL hoặc Body)
    // Tùy vào bản FF, bạn cần bắt đúng Header hoặc URL chứa UID
    let url = $request.url;
    let match = url.match(/uid=(\d+)/); // Tìm đoạn uid= trong link game
    
    if (!match) {
        $done({}); // Không thấy UID thì cho qua hoặc chặn tùy bạn
        return;
    }

    let currentUID = match[1];

    // 2. Tải danh sách từ GitHub
    $httpClient.get(uid_list_url, function(error, response, data) {
        if (error) { $done({}); return; }
        
        let obj = JSON.parse(data);
        let user = obj.users[currentUID];
        let now = new Date();

        // 3. Logic chặn
        if (!user) {
            // UID không tồn tại -> Trả về 403
            $done({ response: { status: 403, body: "SOLITUDE: UID NOT FOUND" } });
        } else if (user.expiry && user.expiry !== "Vĩnh viễn" && new Date(user.expiry) < now) {
            // Hết hạn -> Trả về 403
            $done({ response: { status: 403, body: "SOLITUDE: EXPIRED" } });
        } else {
            // Hợp lệ -> Cho vào game
            $done({});
        }
    });
}

checkUID();
