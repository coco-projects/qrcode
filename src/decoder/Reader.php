<?php

    namespace Coco\qrcode\decoder;

interface Reader
{
    public function decode(BinaryBitmap $image);

    public function reset();
}
