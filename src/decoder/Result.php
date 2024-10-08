<?php

    namespace Coco\qrcode\decoder;

    /**
     * <p>Encapsulates the result of decoding a barcode within an image.</p>
     *
     * @author Sean Owen
     */
final class Result
{
    /**
     * @var mixed[]|mixed
     */
    private $resultMetadata = null;
    private $timestamp;

    public function __construct(private $text, private $rawBytes, private $resultPoints, private $format, $timestamp = '')
    {
        $this->timestamp = $timestamp ? : time();
    }

    /**
     * @return string raw text encoded by the barcode
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return array|string raw bytes encoded by the barcode, if applicable, otherwise {@code null}
     */
    public function getRawBytes()
    {
        return $this->rawBytes;
    }

    /**
     * @return array points related to the barcode in the image. These are typically points
     *         identifying finder patterns or the corners of the barcode. The exact meaning is
     *         specific to the type of barcode that was decoded.
     */
    public function getResultPoints()
    {
        return $this->resultPoints;
    }

    /**
     * @return {@link BarcodeFormat} representing the format of the barcode that was decoded
     */
    public function getBarcodeFormat()
    {
        return $this->format;
    }

    /**
     * @return {@link Map} mapping {@link ResultMetadataType} keys to values. May be
     *   {@code null}. This contains optional metadata about what was detected about the barcode,
     *   like orientation.
     */
    public function getResultMetadata()
    {
        return $this->resultMetadata;
    }

    public function putMetadata(string $type, $value): void
    {
        $resultMetadata = [];
        if ($this->resultMetadata === null) {
            $this->resultMetadata = [];
        }
        $resultMetadata[$type] = $value;
    }

    public function putAllMetadata($metadata): void
    {
        if ($metadata !== null) {
            if ($this->resultMetadata === null) {
                $this->resultMetadata = $metadata;
            } else {
                $this->resultMetadata = array_merge($this->resultMetadata, $metadata);
            }
        }
    }

    public function addResultPoints($newPoints): void
    {
        $oldPoints = $this->resultPoints;
        if ($oldPoints === null) {
            $this->resultPoints = $newPoints;
        } elseif ($newPoints !== null && (is_countable($newPoints) ? count($newPoints) : 0) > 0) {
            $allPoints          = fill_array(0, (is_countable($oldPoints) ? count($oldPoints) : 0) + (is_countable($newPoints) ? count($newPoints) : 0), 0);
            $allPoints          = arraycopy($oldPoints, 0, $allPoints, 0, is_countable($oldPoints) ? count($oldPoints) : 0);
            $allPoints          = arraycopy($newPoints, 0, $allPoints, is_countable($oldPoints) ? count($oldPoints) : 0, is_countable($newPoints) ? count($newPoints) : 0);
            $this->resultPoints = $allPoints;
        }
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function toString()
    {
        return $this->text;
    }
}
