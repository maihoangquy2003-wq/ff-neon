/* HQUY TA - HARD LOCK SCRIPT V1.0 
   Dành cho mục đích nghiên cứu cấu trúc dữ liệu game
*/

let body = $response.body;
if (body) {
    // 1. Can thiệp vào FOV (Tầm quét) - Để giá trị lớn để quét toàn màn hình
    body = body.replace(/fov_range: \d+/g, "fov_range: 180");
    
    // 2. Can thiệp vào Smooth (Độ nhạy) - Để 0 hoặc 1 để khóa khựng (Gắt)
    body = body.replace(/smooth: \d+/g, "smooth: 0");
    
    // 3. Can thiệp vào Target (Vị trí) - 0: Đầu, 1: Cổ, 2: Ngực
    body = body.replace(/lock_part: \d+/g, "lock_part: 0");
    
    // 4. Bypass kiểm tra dữ liệu từ Server
    body = body.replace(/is_verified: false/g, "is_verified: true");
    
    $done({ body });
} else {
    $done({});
}
