<?php


require '../config.php';

if ($_SERVER['HTTP_X_API_TOKEN'] === $token && $_REQUEST['data']) {
    $url = $urlBot . $botToken;
    $text = $_REQUEST['data'];
    list($api, $type, $method, $value) = explode("/", $text);

    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../time_stat', date("Y-m-d H:i:s") . " - {$value}\n", FILE_APPEND);

    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../time_online', time());
    $text = '';
    $notification = (int)file_get_contents(__DIR__ . '/../tg_settings.pause');
    if ($value === 'DOOR') {
        $time = filemtime($_SERVER['DOCUMENT_ROOT'] . '/../time_door');
       // if (time() - $time > 60)
        {
            $text = 'Дверь открыта' . ((time() - $time > 60) ?' ':' 2');
            $chat_id = $notification ? $chatId : $adminId;
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../time_door', date("Y-m-d H:i:s\n"));
        }
    } elseif ($value === 'RING') {
        $text = 'Звонят в дверь';
        $chat_id = $notification ? $chatId : $adminId;
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../time_ring', date("Y-m-d H:i:s\n"));
    } elseif ($value === 'CONNECT') {
        $text = 'Подключен к сети';
        $chat_id = $adminId;
    }

    if ($text) {
        $data = [
            'text' => $text,
            //  'parse_mode' => 'HTML',
            'chat_id' => $chat_id,
            'disable_notification' => (bool)$notification
        ];
        $type = "sendMessage";

        /*
        api/set/door/CONNECT
        api/set/door/ONLINE
        api/set/door/DOOR
        api/set/door/RING
        */


//var_dump(function_exists("curl_init"));
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url . '/' . $type,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);
        $response = curl_exec($curl);

        curl_close($curl);
    }

    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . '/../log.txt',
        print_r(
            [
                'SERVER' => $_SERVER,
                'REQUEST' => $_REQUEST,
                'input' => file_get_contents('php://input'),
                'response' => $response
            ],
            1
        ),
        FILE_APPEND
    );
    //echo $_SERVER['HTTP_USER_AGENT'] . " - ".rand(1111,9999);
} else {
    header("HTTP/1.0 402 Not Found");
    echo "<h1>404</h1><p>No Found</p>";
}
