<?php

    use Coco\qrcode\encoder\QRencode;

    require '../vendor/autoload.php';

    $url = 'https://baidu.com';

    $encoder = QRencode::factory(10);

//    $encoder->formatJpg();
//    $encoder->ToFile($url, './aa.jpg');

    $encoder->formatPng();
    $encoder->ToFile($url, './aa.png');
