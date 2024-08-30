<?php

    namespace Coco\qrcode\decoder;

    /**
     * Thrown when a barcode was not found in the image. It might have been
     * partially detected but could not be confirmed.
     *
     * @author Sean Owen
     */
final class NotFoundException extends ReaderException
{
    private static ?NotFoundException $instance = null;

    public static function getNotFoundInstance(string $message = ""): self
    {
        if (!self::$instance) {
            self::$instance = new NotFoundException($message);
        }

        return self::$instance;
    }
}
