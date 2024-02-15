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
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../time_door', date("Y-m-d H:i:s\n"));
        if (time() - $time > 60) {
            $text = 'Дверь открыта';
            $chat_id = $notification ? $chatId : $adminId;
            {
                $NNpath = __DIR__ . '/img/';
                $NNcurl = curl_init();
                $NNfile = array_slice(scandir($NNpath, SCANDIR_SORT_DESCENDING), 0, 1)[0];
                $time = filemtime($NNpath.DIRECTORY_SEPARATOR.$NNfile);
                $actual = (time() - $time ) < 20;
                $NNtext = "Дверь открыта ";
                $NNname = preg_replace('#(CAPTURE_)(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})\.jpg#', '$5:$6:$7', $NNfile);
                if ($actual) {
                    $NNtext .= $NNname . "\n<a href='https://control.nemin.ru/img/{$NNfile}'>&#8205;</a>";
                } else {
                    $NNtext .= "\n...";
                }
                $NNdata = [
                    'chat_id' => $chatIdTest,
                    'parse_mode' => 'html',
                    'text' => $NNtext,
                ];
                curl_setopt_array($NNcurl, [
                    CURLOPT_URL => $urlBot . $botToken . '/sendMessage',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => http_build_query($NNdata),
                ]);
                $result = curl_exec($NNcurl);

                $result = json_decode($result,true);
                $id = $result['result']['message_id'];
                if (!$actual && $id) {
                    $command = 'php ../wait_photo.php %s > /dev/null &';
                    exec(sprintf($command, $id));
                }
            }
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
            'disable_notification' => ! (bool) $notification
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

} else {
    header("HTTP/1.0 402 Not Found");
    echo "<h1>404</h1><p>No Found</p>";
}
