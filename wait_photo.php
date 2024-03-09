<?php

@set_time_limit(900);

require 'config.php';
require_once 'config.php';
function getAverageLuminance($image)
{
    $luminance_running_sum = 0;
    $x_dimension = imagesx($image);
    $y_dimension = imagesy($image);
    for ($x = 0; $x < $x_dimension; $x++) {
        for ($y = 0; $y < $y_dimension; $y++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $luminance_running_sum += (0.30 * $r) + (0.59 * $g) + (0.11 * $b);
        }
    }
    $total_pixels = $x_dimension * $y_dimension;
    return round($luminance_running_sum / $total_pixels, 0);
}

function getBr($file)
{
    if (file_exists($file) && substr($file, -4) === '.jpg') {
        $image = imagecreatefromjpeg($file);
        return getAverageLuminance($image);
    }
    return 0;
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
        break;
    } else {
        $NNtext .= "\n{$delta}_" . str_repeat('.',$x+1);
$curl = curl_init();
$text = "Дверь открыта";
$data = [
    'chat_id' => $chatIdTest,
    'parse_mode' => 'html',
    'text' => $text,
];
curl_setopt_array($curl, [
    CURLOPT_URL => $urlBot . $botToken . '/sendMessage',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => http_build_query($data),
]);
$result = curl_exec($curl);

$result = json_decode($result, true);
$id = $result['result']['message_id'];


$path = __DIR__ . '/public_html/img/';
$now = time();
for ($x = 0; $x < 60; $x++) {
    $files = [
        'next' => [],
        'prev' => [],
    ];
    foreach (array_slice(scandir($path, SCANDIR_SORT_DESCENDING), 0, 20) as $file) {
        $time = DateTime::createFromFormat('Ymd_His', substr($file, 0, 15))->format('U');

        $delta = $time - $now;
        if (($delta >= 4 && $delta < 60)) {
            if (getBr($path . $file) >= 13) {
                $files['next'][abs($delta)] = substr($file, 0, -4);
            }
        }
        if (($delta > -60 && $delta <= -2)) {
            if (getBr($path . $file) >= 13) {
                $files['prev'][abs($delta)] = substr($file, 0, -4);
            }
        }
    }
    ksort($files['next']);
    ksort($files['prev']);
    $names = [];
    $next = true;
    // Берем из каждого первый и третий кадр
    if (($c = count($files['prev'])) >= 2) {
        $names[] = array_slice($files['prev'], ($c > 2) ? 2 : 1, 1)[0];
        $names[] = array_slice($files['prev'], 0, 1)[0];
    }
    if (($c = count($files['next'])) >= 2) {
        if ($c >= 4) {
            $names[] = array_slice($files['next'], 4, 1)[0];
            $names[] = array_slice($files['next'], 2, 1)[0];
            $next = count($files['next']) <= 7;
        } else {
            $names[] = array_slice($files['next'], 2, 1)[0];
            $names[] = array_slice($files['next'], 0, 1)[0];
        }
    }
    $delta = time() - $now;
    $text = "Дверь открыта ({$delta} сек {$x}/60)\n" . (DateTime::createFromFormat('U', $now)->setTimezone(
            (new DateTimeZone('Europe/Moscow'))
        )->format("H:i:s\n"));
    }
    $NNdata = [
    if ($names) {
        $text .= "\n<a href='https://control.nemin.ru/img.php?id=" . $fileNames . "'>&#8205;</a>";
    } elseif ($next) {
        $text .= str_repeat('.', $x + 1);
    }
    $data = [
        'chat_id' => $chatIdTest,
        'parse_mode' => 'html',
        'message_id' => $id,
        'text' => $text,
    ];
    curl_setopt_array($curl, [
        CURLOPT_URL => $urlBot . $botToken . '/editMessageText',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($data),
    ]);
    curl_exec($NNcurl);
}
    curl_exec($curl);
    if ($next) {
        sleep(2);
    } else {
        break;
    }
}
curl_close($curl);