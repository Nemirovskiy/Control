<?php

require 'config.php';

$time = filemtime($_SERVER['DOCUMENT_ROOT'] . '/../time_online');
//echo $_SERVER['DOCUMENT_ROOT'];
echo "\nONLINE: ".(time() - $time);