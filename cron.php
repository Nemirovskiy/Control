<?php

require 'config.php';

$time = filemtime(__DIR__ . '/time_online');
//echo $_SERVER['DOCUMENT_ROOT'];

$isSend = file_exists(__DIR__ . '/offline.lock');
if ((time() - $time) > 80) {
    if (!$isSend) {
        $url = $urlBot . $botToken;
        $data = [
            'text' => 'OFF Line',
            'chat_id' => $adminId,
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
        file_put_contents('offline.lock', time());
    }
} elseif ($isSend) {
    $url = $urlBot . $botToken;
    $data = [
        'text' => 'ON Line',
        'chat_id' => $adminId,
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
    unlink('offline.lock');
}

// Очистка старых фото
$path = __DIR__ . '/public_html/img/';
$files = array_slice(scandir($path, SCANDIR_SORT_ASCENDING), 0, 103);
$countRemove = 0;
foreach ($files as $file) {
    if (substr(strtolower($file), -4) === '.jpg') {
        $now = new DateTime();
        $interval = new DateInterval("P3D");
        $interval->invert = 1;
        $now->add($interval);
        // CAPTURE_20231126
        $tfile = substr($file, 8, 8);
        $fnow = DateTime::createFromFormat('Ymd', $tfile);
        if ($fnow->getTimestamp() < $now->getTimestamp()) {
            if (unlink($path . $file)) {
                $countRemove++;
            }
        }
    }
}
$timeWokeUp = DateTime::createFromFormat('U', filemtime(__DIR__ . '/morning_woke_up.lock'));
$timeWokeUp->setTimezone(new DateTimeZone('Europe/Moscow'));
$timeWokeUp->add(new \DateInterval('PT3600S'));

$d = new DateTime();
$d->setTimezone(new DateTimeZone('Europe/Moscow'));
if ($d->format('m-d_H:i') === $timeWokeUp->format('m-d_H:i')) {
    $url = $urlBot . $botToken;
    $data = [
        'text' => "Утренний контроль:\n * давление\n * утренние таблетки\n * стакан воды",
        'chat_id' => $chatId,
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

if ($d->format('H:i') === '20:15') {
    $url = $urlBot . $botToken;
    $data = [
        'text' => "Вечерний контроль:\n * давление\n * поужинать с творожком\n * убрана ли еда с обеда",
        'chat_id' => $chatId,
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



if (0 && $d->format('H:i') === '15:30') {
    $url = $urlBot . $botToken;
    $data = [
        'text' => "Дневной контроль:\n * давление\n * Обед",
        'chat_id' => $chatId,
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


if ($countRemove) {
    $textLog = date('Y-m-d H:i:s') . "\tremove {$countRemove} files\n";
    file_put_contents('cron_log.log', $textLog, FILE_APPEND);
}

$files = array_slice(scandir($path, SCANDIR_SORT_DESCENDING), 0, 1);

foreach ($files as $file) {
    if (substr(strtolower($file), -4) === '.jpg') {
        $now = (new DateTime())->format('Y-m-d');
        // CAPTURE_20231205_215933
        $tfile = substr($file, 8, 15);
        $fnow = DateTime::createFromFormat('Ymd_His', $tfile);
        $lock = file_get_contents('morning_woke_up.lock');
        if ($lock !== $fnow->format('Y-m-d') && $fnow->format('H') >= 7) {
            $url = $urlBot . $botToken;
            $data = [
                'text' => "Бабушка проснулась!",
                'chat_id' => $chatId,
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
            file_put_contents('morning_woke_up.lock', $now);
        }
    }
}