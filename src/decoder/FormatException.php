<?php

    namespace Coco\qrcode\decoder;

    /**
     * Thrown when a barcode was successfully detected, but some aspect of
     * the content did not conform to the barcode's format rules. This could have
     * been due to a mis-detection.
     *
     * @author Sean Owen
     */
final class FormatException extends ReaderException
{
    private static ?FormatException $instance = null;

    public function __construct($cause = null)
    {
        if ($cause) {
            parent::__construct($cause);
        }
    }

    public static function getFormatInstance($cause = null): self
    {
        if (!self::$instance) {
            self::$instance = new FormatException();
        }
        if (self::$isStackTrace) {
            return new FormatException($cause);
        } else {
            return self::$instance;
        }
    }
}
