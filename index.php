<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SOLITUDE PROXY</title>
    <style>
        body { margin:0; background:#000; color:#fff; font-family: Arial, sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .container { text-align:center; }
        h1 { color:#0ff; text-shadow:0 0 20px cyan; }
        button { padding:12px 24px; margin:10px; background:#0ff; color:#000; border:none; border-radius:20px; font-weight:bold; cursor:pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚡ SOLITUDE PROXY</h1>
        <div id="loginArea">
            <input type="text" id="username" placeholder="Tài khoản"><br>
            <input type="password" id="password" placeholder="Mật khẩu"><br>
            <button onclick="login()">Đăng nhập</button>
        </div>
        <div id="mainArea" style="display:none;">
            <input type="text" id="key" placeholder="Nhập key"><br>
            <button onclick="activate()">Kích hoạt</button>
            <p id="status"></p>
        </div>
    </div>

    <script>
        // Một vài hàm mẫu, sau sẽ gọi tới api.php
        function login() {
            const u = document.getElementById('username').value;
            const p = document.getElementById('password').value;
            // Gọi API sau
            document.getElementById('loginArea').style.display = 'none';
            document.getElementById('mainArea').style.display = 'block';
        }
        function activate() {
            const key = document.getElementById('key').value;
            document.getElementById('status').innerText = 'Đã gửi yêu cầu kích hoạt key: ' + key;
        }
    </script>
</body>
</html>