<?php
$token = "8626643586:AAET1UUTKaGzDCit47o3UMAYKHdyGQLKSN0";
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['callback_query'])) {
    $callback = $data['callback_query'];
    $chat_id = $callback['message']['chat']['id'];
    $data_cmd = $callback['data'];

    if ($data_cmd == "free_off") {
        file_put_contents('config.json', json_encode(["free_status" => "closed"]));
        file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=🔒 SERVER FREE ĐÃ ĐÓNG THÀNH CÔNG");
    }
    if ($data_cmd == "free_on") {
        file_put_contents('config.json', json_encode(["free_status" => "open"]));
        file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=🔓 SERVER FREE ĐÃ MỞ THÀNH CÔNG");
    }
}
?>
