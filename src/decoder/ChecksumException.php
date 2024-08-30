<?php

    namespace Coco\qrcode\decoder;

    /**
     * Thrown when a barcode was successfully detected and decoded, but
     * was not returned because its checksum feature failed.
     *
     * @author Sean Owen
     */
final class ChecksumException extends ReaderException
{
    private static ?ChecksumException $instance = null;

    public static function getChecksumInstance($cause = ""): self
    {
        if (self::$isStackTrace) {
            return new ChecksumException($cause);
        } else {
            if (!self::$instance) {
                self::$instance = new ChecksumException($cause);
            }

            return self::$instance;
        }
    }
}
