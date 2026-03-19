<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLITUDE PROXY - PREMIUM</title>
    <style>
        :root { --neon-blue: #00f2ff; --neon-green: #00ff88; --neon-red: #ff3131; --bg: #080808; }
        body { background: var(--bg); color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        
        .card { width: 350px; background: #0f0f0f; border-radius: 30px; padding: 25px; border: 1px solid #222; box-shadow: 0 20px 50px rgba(0,0,0,1); text-align: center; border-top: 3px solid var(--neon-blue); }
        .title { font-size: 30px; font-weight: 900; letter-spacing: 3px; color: var(--neon-blue); text-shadow: 0 0 15px var(--neon-blue); margin-bottom: 5px; }
        .version { font-size: 10px; color: #444; margin-bottom: 20px; }

        /* Bảng thông tin giống ảnh mẫu */
        .info-panel { background: rgba(255,255,255,0.03); border: 1px dashed #333; border-radius: 15px; padding: 15px; text-align: left; font-size: 13px; margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .label { color: #888; }
        .value { color: var(--neon-green); font-weight: bold; }

        /* Nút bấm riêng biệt */
        .btn { width: 100%; padding: 15px; border-radius: 15px; font-weight: 800; border: none; cursor: pointer; text-transform: uppercase; margin-bottom: 12px; display: block; text-decoration: none; transition: 0.3s; box-sizing: border-box; }
        
        /* Nút Install Solitude - Màu xanh lá */
        .btn-solitude { background: var(--neon-green); color: black; box-shadow: 0 0 20px rgba(0,255,136,0.3); }
        .btn-solitude:hover { transform: scale(1.03); box-shadow: 0 0 30px var(--neon-green); }

        /* Nút Install Cert - Màu xanh Neon rỗng */
        .btn-cert { background: transparent; border: 2px solid var(--neon-blue); color: var(--neon-blue); }
        .btn-cert:hover { background: var(--neon-blue); color: black; box-shadow: 0 0 20px var(--neon-blue); }

        /* Nút Đăng xuất - Màu đỏ */
        .btn-logout { background: transparent; color: var(--neon-red); font-size: 12px; margin-top: 10px; border: 1px solid transparent; }
        .btn-logout:hover { border-color: var(--neon-red); border-radius: 10px; }

        input { width: 100%; padding: 12px; background: #000; border: 1px solid #333; border-radius: 10px; color: white; text-align: center; margin-bottom: 15px; box-sizing: border-box; outline: none; }
        input:focus { border-color: var(--neon-blue); }
    </style>
</head>
<body>

<div class="card">
    <div class="title">SOLITUDE</div>
    <div class="title" style="font-size: 20px; margin-top: -10px;">PROXY</div>
    <div class="version">NEON OVERDRIVE V7.1</div>

    <div class="info-panel">
        <div class="info-row">
            <span class="label">USER ID:</span>
            <span class="value" id="res-uid">1953348993</span>
        </div>
        <div class="info-row">
            <span class="label">HẠN SỬ DỤNG:</span>
            <span class="value">2026-03-19</span>
        </div>
        <div class="info-row">
            <span class="label">MÁY CHỦ:</span>
            <span class="value" style="color: #ff00ff;">SOLITUDE PREMIUM</span>
        </div>
    </div>

    <input type="text" id="uid_input" placeholder="NHẬP UID ĐỂ CẬP NHẬT">

    <a href="<?php echo LINK_SHADOW_CONF; ?>" class="btn btn-solitude">📥 INSTALL SOLITUDE</a>
    <a href="<?php echo LINK_CERT_RIENG; ?>" class="btn btn-cert">📜 INSTALL CERTIFICATE</a>

    <button class="btn btn-logout" onclick="location.reload()">ĐĂNG XUẤT</button>
</div>

<script>
    // Logic cập nhật UID hiển thị khi nhập
    document.getElementById('uid_input').addEventListener('input', function() {
        document.getElementById('res-uid').innerText = this.value || '1953348993';
    });
</script>

</body>
</html>
