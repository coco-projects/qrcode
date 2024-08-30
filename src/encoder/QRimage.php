<?php

    namespace Coco\qrcode\encoder;

class QRimage
{
    public static function pngToFile($frame, $filename, $pixelPerPoint = 4, $outerFrame = 4): void
    {
        $image = self::frameToImage($frame, $pixelPerPoint, $outerFrame);
        ImagePng($image, $filename);
        ImageDestroy($image);
    }

    public static function pngToBrowser($frame, $pixelPerPoint = 4, $outerFrame = 4): void
    {
        $image = self::frameToImage($frame, $pixelPerPoint, $outerFrame);
        Header("Content-type: image/png");
        ImagePng($image);
        ImageDestroy($image);
    }

    public static function jpgToFile($frame, $filename, $pixelPerPoint = 4, $outerFrame = 4, $q = 85): void
    {
        $image = self::frameToImage($frame, $pixelPerPoint, $outerFrame);

        ImageJpeg($image, $filename, $q);
        ImageDestroy($image);
    }

    public static function jpgToBrowser($frame, $pixelPerPoint = 4, $outerFrame = 4, $q = 85): void
    {
        $image = self::frameToImage($frame, $pixelPerPoint, $outerFrame);
        Header("Content-type: image/jpeg");
        ImageJpeg($image, null, $q);
        ImageDestroy($image);
    }

    private static function frameToImage($frame, $pixelPerPoint = 4, $outerFrame = 4)
    {
        $h          = count($frame);
        $w          = strlen($frame[0]);
        $imgW       = $w + 2 * $outerFrame;
        $imgH       = $h + 2 * $outerFrame;
        $base_image = ImageCreate($imgW, $imgH);
        $col[0]     = ImageColorAllocate($base_image, 255, 255, 255);
        $col[1]     = ImageColorAllocate($base_image, 0, 0, 0);

        imagefill($base_image, 0, 0, $col[0]);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ($frame[$y][$x] == '1') {
                    ImageSetPixel($base_image, $x + $outerFrame, $y + $outerFrame, $col[1]);
                }
            }
        }

        $resultImage = ImageCreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
        ImageCopyResized($resultImage, $base_image, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH);
        ImageDestroy($base_image);

        return $resultImage;
    }
}
