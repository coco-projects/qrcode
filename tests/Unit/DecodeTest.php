<?php

    declare(strict_types = 1);

    namespace Coco\Tests\Unit;

    use Coco\qrcode\decoder\QrReader;
    use Coco\qrcode\encoder\QRencode;
    use PHPUnit\Framework\TestCase;

final class DecodeTest extends TestCase
{
    public function testA()
    {
        $img  = 'examples/aa.jpg';
        $text = 'imagemagick的命令convert可以完成此任务';

        $encoder = QRencode::factory(10);
        $encoder->formatJpg();

        $encoder->ToFile($text, $img);

        sleep(1);
        $qrcode = new QrReader($img);

        $this->assertEquals($text, $qrcode->text());
    }

    public function testB()
    {
        $img  = 'examples/aa.png';
        $text = 'imagemagick的命令convert可以完成此任务';

        $encoder = QRencode::factory(10);
        $encoder->formatPng();

        $encoder->ToFile($text, $img);

        sleep(1);
        $qrcode = new QrReader($img);

        $this->assertEquals($text, $qrcode->text());
    }
}
