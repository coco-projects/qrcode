<?php

    namespace Coco\qrcode\decoder;

    /**
     * This object extends LuminanceSource around an array of YUV data returned from the camera driver,
     * with the option to crop to a rectangle within the full data. This can be used to exclude
     * superfluous pixels around the perimeter and speed up decoding.
     *
     * It works for any pixel format where the Y channel is planar and appears first, including
     * YCbCr_420_SP and YCbCr_422_SP.
     *
     * @author dswitkin@google.com (Daniel Switkin)
     */
final class PlanarYUVLuminanceSource extends LuminanceSource
{
    private static int $THUMBNAIL_SCALE_FACTOR = 2;
    private $dataWidth;
    private $dataHeight;
    private $left;
    private $top;

    public function __construct(private $yuvData, $dataWidth, $dataHeight, $left, $top, $width, $height, $reverseHorizontal)
    {
        parent::__construct($width, $height);

        if ($left + $width > $dataWidth || $top + $height > $dataHeight) {
            throw new \InvalidArgumentException("Crop rectangle does not fit within image data.");
        }
        $this->dataWidth  = $dataWidth;
        $this->dataHeight = $dataHeight;
        $this->left       = $left;
        $this->top        = $top;
        if ($reverseHorizontal) {
            $this->reverseHorizontal($width, $height);
        }
    }

    public function rotateCounterClockwise(): void
    {
        throw new \RuntimeException("This LuminanceSource does not support rotateCounterClockwise");
    }

    public function rotateCounterClockwise45(): void
    {
        throw new \RuntimeException("This LuminanceSource does not support rotateCounterClockwise45");
    }

    public function getRow($y, $row = null)
    {
        if ($y < 0 || $y >= $this->getHeight()) {
            throw new \InvalidArgumentException("Requested row is outside the image: " + $y);
        }
        $width = $this->getWidth();
        if ($row == null || (is_countable($row) ? count($row) : 0) < $width) {
            $row = []; //new byte[width];
        }
        $offset = ($y + $this->top) * $this->dataWidth + $this->left;
        $row    = arraycopy($this->yuvData, $offset, $row, 0, $width);

        return $row;
    }


    public function getMatrix()
    {
        $width  = $this->getWidth();
        $height = $this->getHeight();

        // If the caller asks for the entire underlying image, save the copy and give them the
        // original data. The docs specifically warn that result.length must be ignored.
        if ($width == $this->dataWidth && $height == $this->dataHeight) {
            return $this->yuvData;
        }

        $area        = $width * $height;
        $matrix      = []; //new byte[area];
        $inputOffset = $this->top * $this->dataWidth + $this->left;

        // If the width matches the full width of the underlying data, perform a single copy.
        if ($width == $this->dataWidth) {
            $matrix = arraycopy($this->yuvData, $inputOffset, $matrix, 0, $area);

            return $matrix;
        }

        // Otherwise copy one cropped row at a time.
        $yuv = $this->yuvData;
        for ($y = 0; $y < $height; $y++) {
            $outputOffset = $y * $width;
            $matrix       = arraycopy($this->yuvData, $inputOffset, $matrix, $outputOffset, $width);
            $inputOffset  += $this->dataWidth;
        }

        return $matrix;
    }

    // @Override
    public function isCropSupported(): bool
    {
        return true;
    }

    // @Override
    public function crop($left, $top, $width, $height): PlanarYUVLuminanceSource
    {
        return new PlanarYUVLuminanceSource($this->yuvData, $this->dataWidth, $this->dataHeight, $this->left + $left, $this->top + $top, $width, $height, false);
    }

    /**
     * @return int[]
     */
    public function renderThumbnail(): array
    {
        $width       = (int)($this->getWidth() / self::$THUMBNAIL_SCALE_FACTOR);
        $height      = (int)($this->getHeight() / self::$THUMBNAIL_SCALE_FACTOR);
        $pixels      = []; //new int[width * height];
        $yuv         = $this->yuvData;
        $inputOffset = $this->top * $this->dataWidth + $this->left;

        for ($y = 0; $y < $height; $y++) {
            $outputOffset = $y * $width;
            for ($x = 0; $x < $width; $x++) {
                $grey                       = ($yuv[$inputOffset + $x * self::$THUMBNAIL_SCALE_FACTOR] & 0xff);
                $pixels[$outputOffset + $x] = (0xFF000000 | ($grey * 0x00010101));
            }
            $inputOffset += $this->dataWidth * self::$THUMBNAIL_SCALE_FACTOR;
        }

        return $pixels;
    }

    /**
     *
     * @param int $width
     * @param int $height
     *
     * @return void
     */
    private function reverseHorizontal(int $width, int $height): void
    {
        $yuvData = $this->yuvData;
        for ($y = 0, $rowStart = $this->top * $this->dataWidth + $this->left; $y < $height; $y++, $rowStart += $this->dataWidth) {
            $middle = (int)round($rowStart + $width / 2);
            for ($x1 = $rowStart, $x2 = $rowStart + $width - 1; $x1 < $middle; $x1++, $x2--) {
                $temp         = $yuvData[$x1];
                $yuvData[$x1] = $yuvData[$x2];
                $yuvData[$x2] = $temp;
            }
        }
    }
}
