<?php

    use Coco\qrcode\encoder\QRencode;

    require '../vendor/autoload.php';

    $url = 'https://baidu.com';

    $encoder = QRencode::factory(10);

    $encoder->formatJpg();
//    $encoder->formatPng();

    $encoder->toBrowser($url);