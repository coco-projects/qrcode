<?php

    namespace Coco\qrcode\decoder\Common;

    use Coco\qrcode\decoder\Qrcode\Decoder\QRCodeDecoderMetaData;

    /**
     * <p>Encapsulates the result of decoding a matrix of bits. This typically
     * applies to 2D barcode formats. For now it contains the raw bytes obtained,
     * as well as a String interpretation of those bytes, if applicable.</p>
     *
     * @author Sean Owen
     */
final class DecoderResult
{
    /**
     * @var mixed|null
     */
    private mixed $errorsCorrected;
    /**
     * @var mixed|null
     */
    private mixed $erasures;
    /**
     * @var mixed|null
     */
    private mixed $other;


    public function __construct(private $rawBytes, private $text, private $byteSegments, private $ecLevel, private $structuredAppendSequenceNumber = -1, private $structuredAppendParity = -1)
    {
    }

    public function getRawBytes()
    {
        return $this->rawBytes;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getByteSegments()
    {
        return $this->byteSegments;
    }

    public function getECLevel()
    {
        return $this->ecLevel;
    }

    public function getErrorsCorrected()
    {
        return $this->errorsCorrected;
    }

    public function setErrorsCorrected($errorsCorrected): void
    {
        $this->errorsCorrected = $errorsCorrected;
    }

    public function getErasures()
    {
        return $this->erasures;
    }

    public function setErasures($erasures): void
    {
        $this->erasures = $erasures;
    }

    public function getOther()
    {
        return $this->other;
    }

    public function setOther(QRCodeDecoderMetaData $other): void
    {
        $this->other = $other;
    }

    public function hasStructuredAppend(): bool
    {
        return $this->structuredAppendParity >= 0 && $this->structuredAppendSequenceNumber >= 0;
    }

    public function getStructuredAppendParity()
    {
        return $this->structuredAppendParity;
    }

    public function getStructuredAppendSequenceNumber()
    {
        return $this->structuredAppendSequenceNumber;
    }
}
