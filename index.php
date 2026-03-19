<?php
include 'config.php';
$user_ip = $_SERVER['REMOTE_ADDR'];
$user_ua = $_SERVER['HTTP_USER_AGENT'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLITUDE PROXY V7.1</title>
    <style>
        :root { --neon: #00f2ff; --pink: #ff00ff; --green: #00ff88; --bg: #050505; }
        body { background: var(--bg); color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { width: 360px; border: 1px solid #333; padding: 20px; border-radius: 25px; background: rgba(10,10,10,0.95); box-shadow: 0 0 30px rgba(0,0,0,1); text-align: center; border-top: 2px solid var(--neon); }
        .logo { font-size: 28px; font-weight: 900; letter-spacing: 5px; margin-bottom: 5px; background: linear-gradient(to right, var(--neon), var(--pink)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; filter: drop-shadow(0 0 5px var(--neon)); }
        .sub-logo { font-size: 10px; color: #555; margin-bottom: 20px; }
        .ip-badge { background: rgba(0,242,255,0.1); border: 1px solid #222; padding: 8px; border-radius: 50px; font-size: 12px; color: var(--neon); margin-bottom: 20px; display: flex; justify-content: space-around; align-items: center; }
        .server-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 20px; }
        .srv-node { border: 1px solid #222; padding: 10px 5px; border-radius: 10px; font-size: 10px; cursor: pointer; transition: 0.3s; color: #888; }
        .srv-node.active { border-color: var(--neon); color: var(--neon); box-shadow: inset 0 0 10px rgba(0,242,255,0.2); }
        input { width: 100%; box-sizing: border-box; background: #111; border: 1px solid #222; padding: 12px; border-radius: 10px; color: white; text-align: center; margin-bottom: 15px; outline: none; transition: 0.3s; }
        input:focus { border-color: var(--neon); }
        .btn { width: 100%; padding: 14px; border-radius: 12px; font-weight: bold; cursor: pointer; border: none; text-transform: uppercase; margin-bottom: 10px; transition: 0.3s; display: block; text-decoration: none; }
        .btn-install { background: var(--green); color: black; box-shadow: 0 0 15px rgba(0,255,136,0.3); }
        .btn-cert { background: transparent; border: 1px solid var(--neon); color: var(--neon); }
        .btn-main { background: var(--neon); color: black; }
        .hidden { display: none; }
        .status-dot { width: 8px; height: 8px; background: #00ff00; border-radius: 50%; display: inline-block; margin-left: 5px; box-shadow: 0 0 5px #00ff00; }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">SOLITUDE</div>
    <div class="logo" style="font-size: 22px; margin-top: -10px;">PROXY</div>
    <div class="sub-logo">NEON OVERDRIVE V7.1</div>

    <div class="ip-badge">
        <span>🌐 <?php echo $user_ip; ?></span>
        <span>ONLINE <span class="status-dot"></span></span>
    </div>

    <div class="server-grid" id="server-list">
        <div class="srv-node" onclick="setSrv('aim', this)">AIM PROXY</div>
        <div class="srv-node" onclick="setSrv('noantena', this)">NO ANTENA</div>
        <div class="srv-node" onclick="setSrv('free', this)">SERVER FREE</div>
    </div>

    <div id="step-key">
        <input type="text" id="key" placeholder="ENTER LICENSE KEY">
        <button class="btn btn-main" onclick="verifyKey()">KÍCH HOẠT HỆ THỐNG</button>
    </div>

    <div id="step-final" class="hidden">
        <div style="background: #111; padding: 15px; border-radius: 15px; border: 1px dashed #333; margin-bottom: 15px; font-size: 13px; text-align: left;">
            <p>🆔 USER ID: <span id="display-uid" style="float:right; color:var(--green)">...</span></p>
            <p>📅 HẠN DÙNG: <span style="float:right; color:var(--green)">2026-03-17</span></p>
            <p>🛰️ MÁY CHỦ: <span style="float:right; color:var(--pink)">SOLITUDE PREMIUM</span></p>
        </div>
        
        <input type="text" id="uid" placeholder="NHẬP UID FREE FIRE">
        
        <a href="#" id="btn-install-solitude" class="btn btn-install">📥 INSTALL SOLITUDE</a>
        <a href="<?php echo LINK_CERT; ?>" class="btn btn-cert">📜 INSTALL CERTIFICATE</a>
        
        <button class="btn btn-main" onclick="injectSystem()" style="background: #ff3131; color: white;">ĐĂNG XUẤT</button>
    </div>

    <div id="free-msg" class="hidden" style="color:red; font-size: 12px; margin-bottom: 10px;">SERVER FREE ĐÃ ĐÓNG!</div>
</div>

<script>
let currentSrv = '';
const freeStatus = "<?php echo $server_free_status; ?>";

function setSrv(type, el) {
    currentSrv = type;
    document.querySelectorAll('.srv-node').forEach(n => n.classList.remove('active'));
    el.classList.add('active');
    
    // Logic ẩn hiện theo yêu cầu
    if(type === 'free') {
        if(freeStatus === 'OPEN') {
            showStep('final');
        } else {
            alert('Máy chủ Free đã đóng!');
        }
    } else {
        showStep('key');
    }
}

function showStep(step) {
    document.getElementById('step-key').classList.add('hidden');
    document.getElementById('step-final').classList.add('hidden');
    if(step === 'key') document.getElementById('step-key').classList.remove('hidden');
    if(step === 'final') document.getElementById('step-final').classList.remove('hidden');
}

function verifyKey() {
    // Gọi PHP check key và ghi log Tele...
    // Giả sử đúng key:
    showStep('final');
}

function injectSystem() {
    const uid = document.getElementById('uid').value;
    if(!uid) return alert('Nhập UID!');
    document.getElementById('display-uid').innerText = uid;
    
    // Gửi data về Tele theo mẫu của bạn
    fetch('api.php', {
        method: 'POST',
        body: JSON.stringify({uid: uid, ip: '<?php echo $user_ip; ?>', srv: currentSrv})
    });
    alert('ĐANG INJECT PROXY...');
}
</script>
</body>
</html>
