// Server: https://solitudeseveraim.vercel.app/api/core
export default function handler(req, res) {
    const key = req.headers['x-solitude-token'];
    
    // BẪY: Nếu truy cập bằng trình duyệt hoặc không có chìa khóa bí mật
    if (key !== 'MHQ_201229_PRO') {
        const chui = "Anti dex201229(Dex cái địt mẹ mày !)\n".repeat(1000);
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        return res.status(200).send(`
            <center>
                <h1 style="color:red">code by Mai Hoàng Quý</h1>
                <h2 style="color:blue">Sever Solitude Proxy</h2>
                <hr>
                <div style="word-break: break-all; color: gray; font-size: 10px;">${chui}</div>
            </center>
        `);
    }

    // NẾU LÀ NGƯỜI NHÀ: Trả về logic Mod đã được mã hóa Base64 + Đảo ngược (Reverse)
    // Đoạn mã này chứa toàn bộ logic fileinfo, assetindexer của bạn.
    const securePayload = "ZSk7fSkpYmUuYm9keS50cmltKCkucmVwbGFjZSgvXHMvZywgJycpKSA6IChyZXNwb25zZS5ib2R5ID0gcmF3SGV4KTtyZXR1cm4gcmVzcG9uc2U7fWNhdGNoKGUpe3Jlc3BvbnNlLnN0YXR1c0NvZGU9NTAwO3JldHVybiByZXNwb25zZTt9fQ=="; 
    
    res.setHeader('Content-Type', 'text/plain');
    return res.status(200).send(securePayload);
}