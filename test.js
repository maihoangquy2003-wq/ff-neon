/**
 * Chuyển đổi từ Proxy Pin sang Shadowrocket
 * Script xử lý server_url cho Free Fire
 */

async function handleRequest() {
    const url = "https://version.freefire.info/public/smeta";
    
    try {
        let response = await fetch(url);
        let data = await response.json();
        let host = $request.headers['Host'] || $request.headers['host'];

        if (data.vh.includes(host)) {
            let body = JSON.parse($response.body);
            body.server_url = data.s_url;
            $done({ body: JSON.stringify(body) });
        } else {
            $done({});
        }
    } catch (e) {
        $done({});
    }
}

if ($request.path.includes('/live')) {
    handleRequest();
} else {
    $done({});
}