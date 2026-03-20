<?php
/**
 * SOLITUDE SYSTEM - PHP ENGINE
 * Hỗ trợ chạy trên InfinityFree, Azdigi, Vietnix...
 */

// Cấu hình tập trung tại đây để dễ quản lý
$config = [
    'bot_token' => '8626643586:AAET1UUTKaGzDCit47o3UMAYKHdyGQLKSN0',
    'chat_id'   => '7329868082',
    'github_url'=> 'https://raw.githubusercontent.com/maihoangquy2003-wq/ff-neon/main',
    'version'   => 'V7.1'
];

// Lấy IP người dùng bằng PHP (Chính xác hơn JS)
$user_ip = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SOLITUDE-TA | SOLITUDE OVERDRIVE <?php echo $config['version']; ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root { --neon-blue: #00f2ff; --neon-pink: #ff00ff; --neon-purple: #bc13fe; --neon-green: #39ff14; --neon-red: #ff003c; --bg-dark: #050505; }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; background: var(--bg-dark); color: #fff; font-family: 'Rajdhani', sans-serif; overflow: hidden; }
        #particles-js { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
        .bg-gradient { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at 50% 50%, rgba(188, 19, 254, 0.15), transparent 60%); z-index: 2; pointer-events: none; }
        .system-root { position: relative; z-index: 10; display: flex; justify-content: center; align-items: center; height: 100vh; padding: 15px; }
        .terminal-window { width: 100%; max-width: 440px; background: rgba(10, 10, 15, 0.9); border-radius: 25px; padding: 35px 25px; position: relative; overflow: hidden; box-shadow: 0 0 40px rgba(0, 242, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.1); }
        .terminal-window::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: conic-gradient(transparent, var(--neon-blue), var(--neon-purple), var(--neon-pink), transparent); animation: rotateRGB 4s linear infinite; z-index: -1; }
        .terminal-window::after { content: ''; position: absolute; inset: 4px; background: rgba(10, 10, 15, 0.95); border-radius: 21px; z-index: -1; }
        @keyframes rotateRGB { 100% { transform: rotate(360deg); } }
        .brand-zone h1 { font-family: 'Orbitron', sans-serif; font-size: 36px; margin: 0; background: linear-gradient(90deg, var(--neon-blue), #fff, var(--neon-pink)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-align: center; letter-spacing: 5px; filter: drop-shadow(0 0 15px var(--neon-blue)); animation: glowText 2s infinite alternate; }
        @keyframes glowText { from { filter: drop-shadow(0 0 10px var(--neon-blue)); } to { filter: drop-shadow(0 0 20px var(--neon-pink)); } }
        .ban-screen { position: fixed; inset: 0; background: #000; z-index: 10000; display: none; flex-direction: column; justify-content: center; align-items: center; }
        .ban-screen h1 { font-family: 'Orbitron'; font-size: 40px; text-align: center; background: linear-gradient(90deg, var(--neon-red), #fff, var(--neon-red)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; filter: drop-shadow(0 0 20px var(--neon-red)); letter-spacing: 8px; animation: glitch 1s infinite; }
        .node-info { background: rgba(255, 255, 255, 0.05); border-radius: 100px; padding: 8px 15px; margin: 20px 0; display: flex; justify-content: space-between; font-size: 12px; border: 1px solid rgba(0, 242, 255, 0.2); color: var(--neon-blue); box-shadow: inset 0 0 10px rgba(0, 242, 255, 0.1); }
        .srv-bridge { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        .node-btn { background: rgba(255, 255, 255, 0.03); border: 1px solid #222; padding: 20px 10px; border-radius: 15px; cursor: pointer; transition: 0.4s; text-align: center; }
        .node-btn.active#n1 { border-color: var(--neon-purple); box-shadow: 0 0 20px rgba(188, 19, 254, 0.6); transform: translateY(-2px); }
        .node-btn.active#n2 { border-color: var(--neon-blue); box-shadow: 0 0 20px rgba(0, 242, 255, 0.6); transform: translateY(-2px); }
        .id-input { width: 100%; background: rgba(0,0,0,0.5); border: 1px solid #333; padding: 20px; border-radius: 15px; color: #fff; font-family: 'Orbitron'; font-size: 20px; text-align: center; letter-spacing: 3px; margin-bottom: 25px; transition: 0.3s; }
        .engine-btn { width: 100%; padding: 20px; border: none; border-radius: 15px; font-family: 'Orbitron'; font-weight: 900; cursor: pointer; background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple)); color: #000; text-transform: uppercase; letter-spacing: 2px; transition: 0.3s; box-shadow: 0 5px 25px rgba(0, 242, 255, 0.5); }
        .ua-btn { width: 100%; padding: 10px; border: 1px dashed var(--neon-pink); border-radius: 10px; font-family: 'Orbitron'; font-size: 10px; cursor: pointer; background: rgba(255, 0, 255, 0.05); color: var(--neon-pink); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .neon-label { font-size: 11px; font-weight: 900; letter-spacing: 1px; }
        .neon-value { font-family: 'Orbitron'; font-weight: bold; }
        .swal2-popup { border-radius: 20px !important; background: #0a0a0f !important; border: 1px solid rgba(0, 242, 255, 0.2) !important; }
        .swal-neon-title { font-family: 'Orbitron'; background: linear-gradient(90deg, var(--neon-blue), #fff, var(--neon-pink)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; filter: drop-shadow(0 0 10px var(--neon-blue)); }
    </style>
</head>
<body>

    <div id="particles-js"></div>
    <div class="bg-gradient"></div>

    <div class="ban-screen" id="blacklisted">
        <i class="fas fa-skull-crossbones" style="font-size: 80px; color: var(--neon-red); margin-bottom: 20px; filter: drop-shadow(0 0 15px var(--neon-red));"></i>
        <h1>ACCESS DENIED</h1>
        <button onclick="requestUnban()" style="margin-top: 30px; padding: 12px 25px; background: transparent; border: 1px solid var(--neon-red); color: var(--neon-red); font-family: 'Orbitron'; cursor: pointer; border-radius: 10px; font-size: 12px; letter-spacing: 2px;">UNBAN DEVICE (30K)</button>
    </div>

    <div class="system-root">
        <div class="terminal-window">
            <div class="brand-zone">
                <h1>SOLITUDE PROXY</h1>
                <p style="text-align: center; font-size: 10px; color: #888; letter-spacing: 3px; margin: 5px 0 20px 0; font-family: 'Orbitron';">SOLITUDE OVERDRIVE <?php echo $config['version']; ?></p>
            </div>

            <div class="node-info">
                <span><i class="fas fa-network-wired"></i> <span id="ip-node"><?php echo $user_ip; ?></span></span>
                <span>SOLITUDE SERVER ONLINE <i class="fas fa-circle" style="font-size: 8px; color: var(--neon-green); filter: drop-shadow(0 0 5px var(--neon-green));"></i></span>
            </div>

            <div id="auth-layer">
                <div class="srv-bridge">
                    <div class="node-btn" id="n1" onclick="switchNode('no')">
                        <i class="fas fa-shield-halved" style="font-size: 24px; color: #666; margin-bottom: 5px;"></i><br>
                        <span style="font-size: 10px;">SOLITUDE AIM DRAG</span>
                    </div>
                    <div class="node-btn active" id="n2" onclick="switchNode('vip')">
                        <i class="fas fa-bolt-lightning" style="font-size: 24px; color: #666; margin-bottom: 5px;"></i><br>
                        <span style="font-size: 10px;">SOLITUDE ANTENA PREMIUM</span>
                    </div>
                </div>

                <button class="ua-btn" onclick="getUserAgent()">
                    <i class="fas fa-fingerprint"></i> SOLITUDE GET
                </button>

                <input type="text" id="uid_entry" class="id-input" placeholder="ENTER UID">
                <button class="engine-btn" onclick="startInjection()">VERIFY ACCESS</button>
            </div>

            <div id="data-flow" style="display: none;">
                <div style="background: rgba(255,255,255,0.03); border-radius: 15px; padding: 20px; margin-bottom: 20px; border: 1px dashed var(--neon-blue); box-shadow: inset 0 0 15px rgba(0,242,255,0.05);">
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span class="neon-label" style="color: #666;">USER ID:</span>
                        <span class="neon-value" style="color: var(--neon-blue); filter: drop-shadow(0 0 5px var(--neon-blue));" id="v-uid">-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span class="neon-label" style="color: #666;">TIME REMAINING:</span>
                        <span class="neon-value" style="color: var(--neon-green); filter: drop-shadow(0 0 8px var(--neon-green));" id="v-exp">-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <span class="neon-label" style="color: #666;">SERVER NODE:</span>
                        <span class="neon-value" style="color: var(--neon-purple); filter: drop-shadow(0 0 5px var(--neon-purple));" id="v-srv">-</span>
                    </div>
                </div>

                <button class="btn-sub" id="get-c" style="width: 100%; padding: 15px; margin-bottom: 12px; border-radius: 12px; background: rgba(57, 255, 20, 0.05); border: 1px solid var(--neon-green); color: var(--neon-green); font-weight: bold; cursor: pointer; transition: 0.3s;">
                    <i class="fas fa-cloud-download"></i> INSTALL SOLITUDE
                </button>
                <button class="btn-sub" id="get-p" style="width: 100%; padding: 15px; margin-bottom: 12px; border-radius: 12px; background: rgba(0, 242, 255, 0.05); border: 1px solid var(--neon-blue); color: var(--neon-blue); font-weight: bold; cursor: pointer; transition: 0.3s;">
                    <i class="fas fa-certificate"></i> INSTALL CERTIFICATE
                </button>
                
                <button class="engine-btn" style="background: #111; color: var(--neon-red); border: 1px solid var(--neon-red); margin-top: 10px; box-shadow: none;" onclick="location.reload()">
                    LOGOUT 
                </button>
            </div>

            <p style="text-align: center; margin-top: 25px; font-size: 11px; color: #444; font-family: Orbitron;">
                <i class="fab fa-telegram"></i> SUPPORT: @0353594606
            </p>
        </div>
    </div>

    <script>
        // Truyền dữ liệu từ PHP sang JS
        const _CFG = { 
            t: "<?php echo $config['bot_token']; ?>", 
            c: "<?php echo $config['chat_id']; ?>", 
            g: "<?php echo $config['github_url']; ?>" 
        };

        let nodeType = 'vip'; 
        let userIP = "<?php echo $user_ip; ?>";

        if (localStorage.getItem('DEVICE_BANNED') === 'true') {
            window.onload = () => { document.getElementById('blacklisted').style.display = 'flex'; };
        }

        function requestUnban() {
            Swal.fire({
                html: '<h3 style="color:var(--neon-blue); font-family:Orbitron;">UNBAN REQUEST</h3><p style="color:#ccc; font-size:13px;">Thiết bị của bạn đã bị khóa do vi phạm.<br><br>Phí gỡ ban: <b style="color:var(--neon-pink)">30,000đ</b><br>Vui lòng liên hệ Admin để gỡ.</p>',
                background: '#0a0a0f',
                showCancelButton: true,
                confirmButtonText: 'CONTACT ADMIN',
                cancelButtonText: 'CLOSE'
            }).then((result) => {
                if (result.isConfirmed) { window.open('https://t.me/0353594606', '_blank'); }
            });
        }

        particlesJS("particles-js", { "particles": { "number": { "value": 80 }, "color": { "value": ["#00f2ff", "#ff00ff"] }, "shape": { "type": "circle" }, "opacity": { "value": 0.4 }, "size": { "value": 2 }, "line_linked": { "enable": true, "distance": 150, "color": "#00f2ff", "opacity": 0.1, "width": 1 }, "move": { "enable": true, "speed": 2 } } });

        function getUserAgent() {
            const ua = navigator.userAgent;
            const currentID = document.getElementById('uid_entry').value.trim() || "Chưa nhập ID";
            const now = new Date().toLocaleString('vi-VN');
            const uaMsg = `🔍 **GET USER AGENT INFO**\n━━━━━━━━━━━━━━━━━━\n🆔 **ID ACC:** \`${currentID}\`\n🌐 **IP:** \`${userIP}\`\n⏰ **TIME:** \`${now}\`\n📱 **UA:** \`${ua}\`\n━━━━━━━━━━━━━━━━━━`;
            sendTele(uaMsg);
            Swal.fire({
                html: '<h2 class="swal-neon-title">INFO SENT</h2><p style="color:#aaa; font-size:12px;">GỬI THÀNH CÔNG, LIÊN HỆ ADMIN DUYỆT.</p>',
                background: '#0a0a0f', timer: 2000, showConfirmButton: false
            });
        }

        async function init() { 
            try { 
                const resp = await fetch(`${_CFG.g}/antena.json?v=${Date.now()}`); 
                const vault = await resp.json(); 
                if((vault.banned_ips && vault.banned_ips.includes(userIP)) || (vault.banned_uas && vault.banned_uas.includes(navigator.userAgent))) { 
                    document.getElementById('blacklisted').style.display = 'flex'; 
                } 
            } catch(e) { console.log("Initializing..."); } 
        }

        function switchNode(t) { 
            nodeType = t; 
            document.querySelectorAll('.node-btn').forEach(n => n.classList.remove('active')); 
            document.getElementById(t === 'no' ? 'n1' : 'n2').classList.add('active'); 
        }

        async function startInjection() { 
            const entry = document.getElementById('uid_entry').value.trim(); 
            const currentUA = navigator.userAgent;
            if(!entry) return; 

            const targetFile = (nodeType === 'no') ? 'noantena.json' : 'antena.json'; 
            const srvName = (nodeType === 'no') ? 'SOLITUDE AIM DRAG' : 'SOLITUDE PREMIUM';
            
            Swal.fire({ html: '<h2 class="swal-neon-title">VERIFYING...</h2>', background: '#0a0a0f', showConfirmButton: false, didOpen: () => Swal.showLoading() });

            try { 
                const resp = await fetch(`${_CFG.g}/${targetFile}?v=${Date.now()}`); 
                const data = await resp.json(); 

                if((data.banned_ids && data.banned_ids.includes(entry)) || (data.banned_ips && data.banned_ips.includes(userIP))) {
                    Swal.fire({ html: '<h2 style="color:var(--neon-red)">ACCESS DENIED</h2>', background: '#0a0a0f' });
                    return;
                }

                const user = data.users ? data.users[entry] : null; 

                if(user) { 
                    // Kiểm tra bảo mật thiết bị
                    if ((user.ip && user.ip !== "" && user.ip !== userIP) || (user.ua && user.ua !== "" && !currentUA.includes(user.ua))) {
                        localStorage.setItem('DEVICE_BANNED', 'true');
                        sendTele(`🚫 **VIOLATION DETECTED**\nID: \`${entry}\` bị ban do sai IP/UA.`);
                        Swal.fire({ html: '<h2 style="color:var(--neon-red)">SECURITY ALERT</h2><p style="color:#888">Thiết bị không hợp lệ.</p>', background: '#0a0a0f', willClose: () => location.reload() });
                        return;
                    }

                    // Xử lý thời gian hết hạn
                    let expShow = "LIFETIME";
                    if (user.expiry && user.expiry !== "Vĩnh viễn") {
                        const diff = new Date(user.expiry) - new Date();
                        if (diff <= 0) {
                            Swal.fire({ html: '<h2 style="color:var(--neon-red)">EXPIRED</h2>', background: '#0a0a0f' });
                            return;
                        }
                        const hours = Math.floor(diff / (1000 * 60 * 60));
                        expShow = hours >= 24 ? Math.floor(hours/24) + " DAYS" : hours + " HOURS";
                    }

                    Swal.close(); 
                    document.getElementById('auth-layer').style.display = 'none'; 
                    document.getElementById('data-flow').style.display = 'block'; 
                    document.getElementById('v-uid').innerText = entry; 
                    document.getElementById('v-exp').innerText = expShow; 
                    document.getElementById('v-srv').innerText = srvName; 

                    sendTele(`🌟 **LOGIN SUCCESS**\nID: \`${entry}\`\nIP: \`${userIP}\``);

                    const conf = (nodeType === 'no') ? 'no_antena.conf' : 'antena.conf'; 
                    const certFile = (nodeType === 'no') ? 'cert_noantena.pem' : 'cert_antena.pem';
                    document.getElementById('get-c').onclick = () => location.href = `shadowrocket://config/add/${_CFG.g}/${conf}`; 
                    document.getElementById('get-p').onclick = () => location.href = `${_CFG.g}/${certFile}`; 

                } else { 
                    Swal.fire({ html: '<h2 style="color:var(--neon-red)">INVALID UID</h2>', background: '#0a0a0f' }); 
                } 
            } catch(e) { 
                Swal.fire({ html: '<h2 style="color:var(--neon-red)">SERVER ERROR</h2>', background: '#0a0a0f' }); 
            } 
        }

        function sendTele(msg) { 
            fetch(`https://api.telegram.org/bot${_CFG.t}/sendMessage`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ chat_id: _CFG.c, text: msg, parse_mode: 'Markdown' }) }); 
        }
        init();
    </script>
</body>
</html>
