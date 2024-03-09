<?php

$id = $_GET['id'];

$path = __DIR__ . '/img/';

$files = explode('~', $id);

$sizes = (file_exists($path . $files[0] . '.jpg') ? getimagesize($path . $files[0] . '.jpg') : false);
if (!$sizes && $files[1]) {
    $sizes = (file_exists($path . $files[1] . '.jpg') ? getimagesize($path . $files[1] . '.jpg') : false);
}
if (!$sizes) {
    header('Content-type: image/jpeg');
    die(file_get_contents('./empty.png'));
}

[$height, $width] = $sizes;
$delW = 1; // Делитель ширины
$delH = 1; // Делитель высоты
$count = count($files);

if ($count === 2) {
    $width *= 1.5;
    $height *= 0.75;
    $delW = 2;
}
if ($count === 4) {
    $height *= 1.5;
    $width *= 1.5;
    $delH = 2;
    $delW = 2;
}
$resImage = imagecreatetruecolor($width, $height);

for ($i = 0; $i < $count; $i++) {
    $item = $path . $files[$i] . '.jpg';
    if (!file_exists($item)) {
        continue;
    }
    $image = imagecreatefromjpeg($item);
    if (!$image) {
        continue;
    }
    $srcImage = imagemirror($image);
    $srcWidth = imagesx($srcImage);
    $srcHeight = imagesy($srcImage);

    if ($count > 1) {
        $time = DateTime::createFromFormat('Ymd_His', substr($files[$i], 0, 15))->format('H:i:s');
        $red = imagecolorallocate($srcImage, 0x00, 0x00, 0x00);
        $black = imagecolorallocate($srcImage, 0xFF, 0xFF, 0xFF);
// Сделаем фон
        //imagefilledrectangle($srcImage, 0, 0, 140, 18, $red);
// Путь к ttf файлу шрифта
        $font_file = './arial.ttf';
// Рисуем текст 'PHP Manual' шрифтом 13го размера
        imagefttext($srcImage, 10, 0, ($srcWidth / 2) - 40, 15, $black, $font_file, "[" . ($i + 1) . "]" . $time);
    }

    $x = ($i % 2 === 0 ? 0 : ($width / $delW));
    $y = ($i >= 2 ? ($height / $delH) : 0);
    $h2 = (int)($height / $delH);
    $w2 = (int)($width / $delW);

    imagecopyresampled($resImage, $srcImage, $x, $y, 0, 0, $w2, $h2, $srcWidth, $srcHeight);
    imagedestroy($srcImage);
}

$rotation = $resImage;
imagealphablending($rotation, false);
imagesavealpha($rotation, true);
imagefilter($rotation, IMG_FILTER_BRIGHTNESS, 80);

function imagemirror($image)
{
    $rotang = 90; // Rotation angle

    $srcImage = imagerotate($image, $rotang, imageColorAllocateAlpha($image, 0, 0, 0, 127));

    // Зеркалируем
    /*    $width = imagesx($srcImage);
        $height = imagesy($srcImage);
        $img = imagecreatetruecolor($width, $height);
    // наносим попиксельно изображение в обратном порядке
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $color=imagecolorat($srcImage, $x,$y);
                imagesetpixel($img, $width-$x, $y, $color);
            }
        }
        return $img;*/
    return $srcImage;
}

header('Content-type: image/jpeg');
imagejpeg($rotation);
imagedestroy($srcImage);