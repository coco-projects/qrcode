<?php

    namespace Coco\qrcode\encoder;

class QRcode
{
    public $version;
    public $width;
    public $data;

    public function encodeMask(QRinput $input, $mask)
    {
        if ($input->getVersion() < 0 || $input->getVersion() > QRSPEC_VERSION_MAX) {
            throw new \Exception('wrong version');
        }

        if ($input->getErrorCorrectionLevel() > QR_ECLEVEL_H) {
            throw new \Exception('wrong level');
        }

        $raw     = new QRrawcode($input);
        $version = $raw->version;
        $width   = QRspec::getWidth($version);
        $frame   = QRspec::newFrame($version);
        $filler  = new FrameFiller($width, $frame);

        // inteleaved data and ecc codes
        for ($i = 0; $i < $raw->dataLength + $raw->eccLength; $i++) {
            $code = $raw->getCode();
            $bit  = 0x80;
            for ($j = 0; $j < 8; $j++) {
                $addr = $filler->next();
                $filler->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
                $bit = $bit >> 1;
            }
        }

        unset($raw);

        // remainder bits
        $j = QRspec::getRemainder($version);
        for ($i = 0; $i < $j; $i++) {
            $addr = $filler->next();
            $filler->setFrameAt($addr, 0x02);
        }

        $frame = $filler->frame;
        unset($filler);

        // masking
        $maskObj = new QRmask();
        if ($mask < 0) {
            if (QR_FIND_BEST_MASK) {
                $masked = $maskObj->mask($width, $frame, $input->getErrorCorrectionLevel());
            } else {
                $masked = $maskObj->makeMask($width, $frame, (intval(QR_DEFAULT_MASK) % 8), $input->getErrorCorrectionLevel());
            }
        } else {
            $masked = $maskObj->makeMask($width, $frame, $mask, $input->getErrorCorrectionLevel());
        }
        if ($masked == null) {
            return null;
        }
        $this->version = $version;
        $this->width   = $width;
        $this->data    = $masked;

        return $this;
    }

    public function encodeInput(QRinput $input)
    {
        return $this->encodeMask($input, -1);
    }

    public function encodeString8bit(string $string, $version, $level)
    {

        $input = new QRinput($version, $level);
        $ret   = $input->append($input, QR_MODE_8, strlen($string), str_split($string));
        if ($ret < 0) {
            unset($input);

            return null;
        }

        return $this->encodeInput($input);
    }

    public function encodeString(string $string, $version, $level, $hint, $casesensitive)
    {
        if ($hint != QR_MODE_8 && $hint != QR_MODE_KANJI) {
            throw new \Exception('bad hint');
        }

        $input = new QRinput($version, $level);

        $ret   = QRsplit::splitStringToQRinput($string, $input, $hint, $casesensitive);

        if ($ret < 0) {
            return null;
        }

        return $this->encodeInput($input);
    }
}
