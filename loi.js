const uid_list_url = "https://raw.githubusercontent.com/maihoangquy2003-wq/ff-neon/main/antena.json";

async function checkUID() {
    let url = $request.url;
    let match = url.match(/uid=(\d+)/); 
    
    if (!match) {
        $done({}); 
        return;
    }

    let currentUID = match[1];

    
    $httpClient.get(uid_list_url, function(error, response, data) {
        if (error) { $done({}); return; }
        
        let obj = JSON.parse(data);
        let user = obj.users[currentUID];
        let now = new Date();

        
        if (!user) {
            $done({ response: { status: 403, body: "SOLITUDE: UID NOT FOUND" } });
        } else if (user.expiry && user.expiry !== "Vĩnh viễn" && new Date(user.expiry) < now) {
            $done({ response: { status: 403, body: "SOLITUDE: EXPIRED" } });
        } else {
            $done({});
        }
    });
}

checkUID();
