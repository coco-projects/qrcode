<?php

    namespace Coco\qrcode\decoder\Qrcode\Decoder;

    /**
     * <p>See ISO 18004:2006, 6.5.1. This enum encapsulates the four error correction levels
     * defined by the QR code standard.</p>
     *
     * @author Sean Owen
     */
class ErrorCorrectionLevel
{
    /**
     * @var ErrorCorrectionLevel[]|null
     */
    private static ?array $FOR_BITS = null;

    public function __construct(private $bits, private $ordinal = 0)
    {
    }

    public static function Init(): void
    {
        self::$FOR_BITS = [

            //M
            new ErrorCorrectionLevel(0x00, 1),
            //L
            new ErrorCorrectionLevel(0x01, 0),
            //H
            new ErrorCorrectionLevel(0x02, 3),
            //Q
            new ErrorCorrectionLevel(0x03, 2),

        ];
    }
    /** L = ~7% correction */
    //  self::$L = new ErrorCorrectionLevel(0x01);
    /** M = ~15% correction */
    //self::$M = new ErrorCorrectionLevel(0x00);
    /** Q = ~25% correction */
    //self::$Q = new ErrorCorrectionLevel(0x03);
    /** H = ~30% correction */
    //self::$H = new ErrorCorrectionLevel(0x02);
    /**
     * @param int $bits containing the two bits encoding a QR Code's error correction level
     *
     * @return null|self representing the encoded error correction level
     */
    public static function forBits(int $bits): self|null
    {
        if ($bits < 0 || $bits >= (is_countable(self::$FOR_BITS) ? count(self::$FOR_BITS) : 0)) {
            throw new \InvalidArgumentException();
        }
        $level = self::$FOR_BITS[$bits];

        // $lev = self::$$bit;
        return $level;
    }


    public function getBits()
    {
        return $this->bits;
    }

    public function toString()
    {
        return $this->bits;
    }

    public function getOrdinal()
    {
        return $this->ordinal;
    }
}

    ErrorCorrectionLevel::Init();
