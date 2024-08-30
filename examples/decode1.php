<?php

    use Coco\qrcode\decoder\QrReader;

    require '../vendor/autoload.php';

    $img = './aa.jpg';
//    $img = './aa.png';

    $qrcode = new QrReader($img);

    echo $qrcode->text();