<?php

    namespace Coco\qrcode\decoder\Common;

    /**
     * <p>Encapsulates the result of detecting a barcode in an image. This includes the raw
     * matrix of black/white pixels corresponding to the barcode, and possibly points of interest
     * in the image, like the location of finder patterns or corners of the barcode in the image.</p>
     *
     * @author Sean Owen
     */
class DetectorResult
{
    public function __construct(private $bits, private $points)
    {
    }

    final public function getBits()
    {
        return $this->bits;
    }

    final public function getPoints()
    {
        return $this->points;
    }
}
