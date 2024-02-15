<?php

require __DIR__ . '/../config.php';

// https://core.telegram.org/bots/api#getting-updates

$input = file_get_contents('php://input');
$input = json_decode($input, true);

file_put_contents(__DIR__ . '/../input.log', print_r($input, 1), FILE_APPEND);

$url = $urlBot . $botToken;
if ($input['message']['chat']['id'] == $adminId) {
    $command = strtolower($input['message']['text']);
    if ($command === '/status') {
        $time = filemtime($_SERVER['DOCUMENT_ROOT'] . '/../time_online');
        $date = DateTime::createFromFormat('U', (int)$time);
        $date->setTimezone(new DateTimeZone('Europe/Moscow'));
        $value = "<b>OnLine:</b>\n";
        $value .= $date->format('Y-m-d H:i:s');
        $diff = $date->diff(new DateTime())->format("%h час %i мин %s сек");
        $diff = str_replace(['0 час', ' 0 мин', ' 0 сек'], '', $diff);
        $value .= " - " . trim($diff);

        $time = filemtime($_SERVER['DOCUMENT_ROOT'] . '/../time_door');
        $date = DateTime::createFromFormat('U', (int)$time);
        $date->setTimezone(new DateTimeZone('Europe/Moscow'));
        $value .= "\n<b>Door:</b>\n";
        $value .= $date->format('Y-m-d H:i:s');
        $diff = $date->diff(new DateTime())->format("%h час %i мин %s сек");
        $diff = str_replace(['0 час', ' 0 мин', ' 0 сек'], '', $diff);
        $value .= " - " . trim($diff);

        $time = filemtime($_SERVER['DOCUMENT_ROOT'] . '/../time_ring');
        $date = DateTime::createFromFormat('U', (int)$time);
        $date->setTimezone(new DateTimeZone('Europe/Moscow'));
        $value .= "\n<b>Ring:</b>\n";
        $value .= $date->format('Y-m-d H:i:s');
        $diff = $date->diff(new DateTime())->format("%h час %i мин %s сек");
        $diff = str_replace(['0 час', ' 0 мин', ' 0 сек'], '', $diff);
        $value .= " - " . trim($diff);

        $notification = ((int)file_get_contents(__DIR__ . '/../tg_settings.pause'));
        $value .= "\n<b>Оповещения</b>: " . ($notification ? 'включено' : 'отключено');

        $data = [
            'text' => $value,
            'chat_id' => $input['message']['chat']['id'],
            'parse_mode' => 'html'
        ];
        $type = "sendMessage";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url . '/' . $type,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);
        $response = curl_exec($curl);

        curl_close($curl);
    } elseif ($command === '/pause') {
        $notification = ((int)file_get_contents(__DIR__ . '/../tg_settings.pause')) ? 0 : 1;
        file_put_contents(__DIR__ . '/../tg_settings.pause', $notification);
        $value = 'Оповещения ' . ($notification ? 'включено' : 'отключено');
        $data = [
            'text' => $value,
            'chat_id' => $input['message']['chat']['id'],
            'parse_mode' => 'html'
        ];
        $type = "sendMessage";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url . '/' . $type,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);
        $response = curl_exec($curl);
    } elseif (strpos($command, '/pressadd') !== false) {
        preg_match("#(\d{2,3})[\/\-](\d{2,3})[\/\-](\d{2,3})#", $input['message']['text'], $match);
        array_shift($match);
        $text = implode("/", $match);

        file_put_contents(__DIR__ . '/../stat_press.csv', $text . PHP_EOL, FILE_APPEND);
    } elseif ($command === '/press') {
        $text = file_get_contents(__DIR__ . '/../stat_press.csv');
        $value = '<pre>' . preg_replace("#(\d+)\t(\d+)\t(\d+)\n#", "$1/$2/$3\n", $text) . '</pre>';
        $data = [
            'text' => $value,
            'chat_id' => $input['message']['chat']['id'],
            'parse_mode' => 'html'
        ];
        $type = "sendMessage";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url . '/' . $type,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);
        $response = curl_exec($curl);
    }
} elseif ($input['message']['chat']['id'] == $chatId || $input['message']['chat']['id'] == $chatIdTest) {
    if (preg_match("#(\d{2,3})[\/\-](\d{2,3})[\/\-](\d{2,3})#", $input['message']['text'], $match) !== false) {
        if (count($match) === 4) {
            array_shift($match);
           {
                $value = implode("/", $match);
                $url = $urlBot . $botToken;
                $data = [
                    'text' => "Сохранено давление: " . $value,
                    'chat_id' => $input['message']['chat']['id'],
                    'disable_notification' => true,
                ];
                $type = "sendMessage";
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url . '/' . $type,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => http_build_query($data),
                ]);
                $response = curl_exec($curl);
                $value = implode("\t", $match);

                curl_close($curl);
                file_put_contents(
                    __DIR__ . '/../stat_press.csv',
                    date("Y-m-d H:i:s") . "\t" . $value . PHP_EOL,
                    FILE_APPEND
                );
            }
        }
    }
}
