<?php

    namespace Coco\qrcode\encoder;

    // Encoding modes
    define('QR_MODE_NUL', -1);
    define('QR_MODE_NUM', 0);
    define('QR_MODE_AN', 1);
    define('QR_MODE_8', 2);
    define('QR_MODE_KANJI', 3);
    define('QR_MODE_STRUCTURE', 4);

    // Levels of error correction.
    define('QR_ECLEVEL_L', 0);
    define('QR_ECLEVEL_M', 1);
    define('QR_ECLEVEL_Q', 2);
    define('QR_ECLEVEL_H', 3);

    // Supported output formats
    define('QR_FORMAT_TEXT', 0);
    define('QR_FORMAT_PNG', 1);

    define('QR_IMAGE', true);
    define('STRUCTURE_HEADER_BITS', 20);
    define('MAX_STRUCTURED_SYMBOLS', 16);
    define('N1', 3);
    define('N2', 3);
    define('N3', 40);
    define('N4', 10);
    define('QRSPEC_VERSION_MAX', 40);
    define('QRSPEC_WIDTH_MAX', 177);
    define('QRCAP_WIDTH', 0);
    define('QRCAP_WORDS', 1);
    define('QRCAP_REMINDER', 2);
    define('QRCAP_EC', 3);

    define('QR_CACHEABLE', false);           // use cache - more disk reads but less CPU power, masks and format templates are stored there
    define('QR_CACHE_DIR', false);           // used when QR_CACHEABLE === true

    define('QR_FIND_BEST_MASK', true);       // if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code
    define('QR_FIND_FROM_RANDOM', 2);        // if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
    define('QR_DEFAULT_MASK', 2);            // when QR_FIND_BEST_MASK === false
    define('QR_PNG_MAXIMUM_SIZE', 1024);     // maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images

class QRencode
{
    public $casesensitive = true;
    public $eightbit      = false;
    public $version       = 0;
    public $size          = 3;
    public $margin        = 4;
    public $level         = QR_ECLEVEL_L;
    public $hint          = QR_MODE_8;
    public $format        = 'png';


    public static function factory($size = 3, $margin = 4, $eightbit = false): static
    {
        $enc           = new static();
        $enc->size     = $size;
        $enc->margin   = $margin;
        $enc->eightbit = $eightbit;

        return $enc;
    }

    public function toBrowser(string $intext): void
    {
        ob_start();
        $frame = $this->encodeFrame($intext);
        ob_get_contents();
        ob_end_clean();

        $maxSize = (int)(QR_PNG_MAXIMUM_SIZE / (count($frame) + 2 * $this->margin));

        if ($this->format == 'png') {
            QRimage::pngToBrowser($frame, min(max(1, $this->size), $maxSize), $this->margin);
        } else {
            QRimage::jpgToBrowser($frame, min(max(1, $this->size), $maxSize), $this->margin);
        }
    }

    public function ToFile(string $intext, string $fileName): void
    {
        $frame = $this->encodeFrame($intext);

        $maxSize = (int)(QR_PNG_MAXIMUM_SIZE / (count($frame) + 2 * $this->margin));

        $dir = dirname($fileName);

        if (!is_file($fileName)) {
            is_dir($dir) or mkdir($dir, 0777, 1);
        }

        if (is_writable($dir)) {
            if ($this->format == 'png') {
                QRimage::pngToFile($frame, $fileName, min(max(1, $this->size), $maxSize), $this->margin);
            } else {
                QRimage::jpgToFile($frame, $fileName, min(max(1, $this->size), $maxSize), $this->margin);
            }
        } else {
            throw new \Exception($fileName . ' error');
        }
    }

    public function formatPng(): static
    {
        $this->format = 'png';

        return $this;
    }

    public function formatJpg(): static
    {
        $this->format = 'png';

        return $this;
    }

    public function errorCorrectionLow(): static
    {
        $this->level = QR_ECLEVEL_L;

        return $this;
    }

    public function errorCorrectionMedium(): static
    {
        $this->level = QR_ECLEVEL_M;

        return $this;
    }

    public function errorCorrectionQuality(): static
    {
        $this->level = QR_ECLEVEL_Q;

        return $this;
    }

    public function errorCorrectionHigh(): static
    {
        $this->level = QR_ECLEVEL_H;

        return $this;
    }

    protected function encodeRAW($intext)
    {
        $code = new QRcode();
        if ($this->eightbit) {
            $code->encodeString8bit($intext, $this->version, $this->level);
        } else {
            $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
        }

        return $code->data;
    }

    protected function encodeFrame($intext)
    {
        $data = $this->encodeRAW($intext);

        return static::binarize($data);
    }

    protected static function binarize($frame)
    {
        $len = count($frame);
        foreach ($frame as &$frameLine) {
            for ($i = 0; $i < $len; $i++) {
                $frameLine[$i] = (ord($frameLine[$i]) & 1) ? '1' : '0';
            }
        }

        return $frame;
    }
}
