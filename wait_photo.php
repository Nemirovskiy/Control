<?php

@set_time_limit(900);

require 'config.php';

$id = $argv[1];
$NNpath = __DIR__ . '/public_html/img/';
$NNcurl = curl_init();
for ($x = 0; $x < 100; $x++) {
    sleep(3);
    $NNfile = array_slice(scandir($NNpath, SCANDIR_SORT_DESCENDING), 0, 1)[0];
    $time = filemtime($NNpath.DIRECTORY_SEPARATOR.$NNfile);
    $delta = time() - $time;
    $NNtext = "Дверь открыта ";
    $NNname = preg_replace('#(CAPTURE_)(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})\.jpg#', '$5:$6:$7', $NNfile);
    if ($delta > 20 ) {
        $NNtext .= $NNname . "\n<a href='https://control.nemin.ru/img/{$NNfile}'>&#8205;</a>";

        $NNdata = [
            'chat_id' => $chatIdTest,
            'parse_mode' => 'html',
            'message_id' => $id,
            'text' => $NNtext,
        ];
        curl_setopt_array($NNcurl, [
            CURLOPT_URL => $urlBot . $botToken . '/editMessageText',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($NNdata),
        ]);
        curl_exec($NNcurl);
        break;
    } else {
        $NNtext .= "\n{$delta}_" . str_repeat('.',$x+1);
    }
    $NNdata = [
        'chat_id' => $chatIdTest,
        'parse_mode' => 'html',
        'message_id' => $id,
        'text' => $NNtext,
    ];
    curl_setopt_array($NNcurl, [
        CURLOPT_URL => $urlBot . $botToken . '/editMessageText',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($NNdata),
    ]);
    curl_exec($NNcurl);
}