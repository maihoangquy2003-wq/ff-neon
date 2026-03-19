<?php
include 'config.php';
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];
// Giả định kiểm tra BAN từ database hoặc file json (Logic Ban sẽ nằm ở api.php)
$is_banned = false; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLITUDE PROXY V7.1</title>
    <style>
        :root { --neon: #00f2ff; --green: #00ff88; --red: #ff3131; --bg: #050505; }
        body { background: var(--bg); color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .card { width: 100%; max-width: 380px; background: #0a0a0a; border-radius: 30px; padding: 25px; border: 1px solid #222; border-top: 3px solid var(--neon); box-shadow: 0 15px 50px rgba(0,0,0,1); text-align: center; }
        .logo { font-size: 32px; font-weight: 900; letter-spacing: 5px; color: var(--neon); text-shadow: 0 0 15px var(--neon); }
        .ip-badge { background: rgba(0,242,255,0.05); padding: 10px; border-radius: 50px; font-size: 12px; color: var(--neon); margin: 20px 0; display: flex; justify-content: space-around; border: 1px solid #1a1a1a; }
        .srv-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .srv-node { border: 1px solid #222; padding: 12px 5px; border-radius: 12px; font-size: 10px; cursor: pointer; color: #555; transition: 0.3s; }
        .srv-node.active { border-color: var(--neon); color: var(--neon); box-shadow: inset 0 0 10px rgba(0,242,255,0.2); }
        .info-panel { background: rgba(255,255,255,0.02); border: 1px dashed #333; border-radius: 15px; padding: 15px; text-align: left; font-size: 13px; margin-bottom: 20px; }
        .btn { width: 100%; padding: 15px; border-radius: 15px; font-weight: 800; border: none; cursor: pointer; text-transform: uppercase; margin-bottom: 12px; display: block; text-decoration: none; transition: 0.3s; box-sizing: border-box; }
        .btn-config { background: var(--green); color: black; }
        .btn-cert { background: transparent; border: 2px solid var(--neon); color: var(--neon); }
        .btn-main { background: var(--neon); color: black; }
        input { width: 100%; padding: 14px; background: #000; border: 1px solid #333; border-radius: 12px; color: white; text-align: center; margin-bottom: 15px; box-sizing: border-box; outline: none; }
        .hidden { display: none; }
        .ban-screen { color: var(--red); border: 2px solid var(--red); padding: 20px; border-radius: 20px; box-shadow: 0 0 20px var(--red); }
    </style>
</head>
<body>

<div class="card">
    <?php if ($is_banned): ?>
        <div class="ban-screen">
            <h2>⚠️ HỆ THỐNG ĐÃ BAN IP ⚠️</h2>
            <p>Phát hiện vi phạm chính sách hoặc mượn UID.</p>
            <button class="btn" style="background:var(--red); color:white;" onclick="sendUnban()">GỬI YÊU CẦU GỠ BAN</button>
        </div>
    <?php else: ?>
        <div class="logo">SOLITUDE</div>
        <div class="ip-badge"><span>🌐 <?php echo $ip; ?></span><span>ONLINE ●</span></div>

        <div class="srv-grid">
            <div class="srv-node" onclick="setSrv('antena', this)">SERVER AIM</div>
            <div class="srv-node" onclick="setSrv('noantena', this)">NO ANTENA</div>
            <div class="srv-node" onclick="setSrv('free', this)">SERVER FREE</div>
        </div>

        <div id="box-key" class="hidden">
            <input type="text" id="key_input" placeholder="ENTER LICENSE KEY">
            <button class="btn btn-main" onclick="verifyKey()">GET INFO & KEY</button>
        </div>

        <div id="box-final" class="hidden">
            <div class="info-panel">
                <p>🆔 UID: <span id="res-uid" style="color:var(--green)">...</span></p>
                <p>🛰️ SERVER: <span id="res-srv" style="color:var(--neon); text-transform:uppercase;">...</span></p>
            </div>
            <input type="text" id="uid_input" placeholder="ENTER UID FREE FIRE">
            <a href="#" id="link-cfg" class="btn btn-config">📥 INSTALL SOLITUDE</a>
            <a href="#" id="link-cert" class="btn btn-cert">📜 INSTALL CERTIFICATE</a>
        </div>

        <div id="box-closed" class="hidden" style="color:var(--red); padding: 10px;">SERVER FREE ĐÃ ĐÓNG!</div>
        
        <a href="<?php echo GROUP_SUPPORT; ?>" style="color:#444; font-size:11px; text-decoration:none;">JOIN TELEGRAM SUPPORT</a>
    <?php endif; ?>
</div>

<script>
let currentType = '';
const links = <?php echo json_encode($links); ?>;
const freeStatus = "<?php echo $server_free_status; ?>";

function setSrv(type, el) {
    currentType = type;
    document.querySelectorAll('.srv-node').forEach(n => n.classList.remove('active'));
    el.classList.add('active');
    
    // Reset ẩn/hiện
    document.getElementById('box-key').classList.add('hidden');
    document.getElementById('box-final').classList.add('hidden');
    document.getElementById('box-closed').classList.add('hidden');

    if (type === 'free') {
        if (freeStatus === "OPEN") {
            document.getElementById('box-final').classList.remove('hidden');
            updateLinks(type);
        } else {
            document.getElementById('box-closed').classList.remove('hidden');
        }
    } else {
        document.getElementById('box-key').classList.remove('hidden');
    }
}

function updateLinks(type) {
    document.getElementById('res-srv').innerText = type;
    document.getElementById('link-cfg').href = links[type].cfg;
    document.getElementById('link-cert').href = links[type].cert;
}

function verifyKey() {
    const key = document.getElementById('key_input').value;
    if(!key) return alert('Vui lòng nhập Key!');
    // Gửi yêu cầu check key về Telegram và GitHub...
    document.getElementById('box-key').classList.add('hidden');
    document.getElementById('box-final').classList.remove('hidden');
    updateLinks(currentType);
    sendTele('GET_KEY_SUCCESS', key);
}

function sendTele(action, data) {
    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: action,
            val: data,
            uid: document.getElementById('uid_input').value,
            ip: "<?php echo $ip; ?>",
            srv: currentType
        })
    });
}

document.getElementById('uid_input').addEventListener('input', function() {
    document.getElementById('res-uid').innerText = this.value || '...';
});
</script>
</body>
</html>
