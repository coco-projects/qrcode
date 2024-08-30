<?php

    namespace Coco\qrcode\decoder\Common;

final class BitMatrix
{
    private $width;
    private $height;
    private $rowSize;
    /**
     * @var mixed|int[]
     */
    private $bits;

    public function __construct($width, $height = false, $rowSize = false, $bits = false)
    {
        if (!$height) {
            $height = $width;
        }
        if (!$rowSize) {
            $rowSize = (int)(($width + 31) / 32);
        }
        if (!$bits) {
            $bits = fill_array(0, $rowSize * $height, 0);
        }
        $this->width   = $width;
        $this->height  = $height;
        $this->rowSize = $rowSize;
        $this->bits    = $bits;
    }

    public static function parse($stringRepresentation, $setString, $unsetString): self
    {
        if (!$stringRepresentation) {
            throw new \InvalidArgumentException();
        }
        $bits        = [];
        $bitsPos     = 0;
        $rowStartPos = 0;
        $rowLength   = -1;
        $nRows       = 0;
        $pos         = 0;
        while ($pos < strlen((string)$stringRepresentation)) {
            if ($stringRepresentation[$pos] == '\n' || $stringRepresentation->{$pos} == '\r') {
                if ($bitsPos > $rowStartPos) {
                    if ($rowLength == -1) {
                        $rowLength = $bitsPos - $rowStartPos;
                    } elseif ($bitsPos - $rowStartPos != $rowLength) {
                        throw new \InvalidArgumentException("row lengths do not match");
                    }
                    $rowStartPos = $bitsPos;
                    $nRows++;
                }
                $pos++;
            } elseif (substr((string)$stringRepresentation, $pos, strlen((string)$setString)) == $setString) {
                $pos            += strlen((string)$setString);
                $bits[$bitsPos] = true;
                $bitsPos++;
            } elseif (substr((string)$stringRepresentation, $pos + strlen((string)$unsetString)) == $unsetString) {
                $pos            += strlen((string)$unsetString);
                $bits[$bitsPos] = false;
                $bitsPos++;
            } else {
                throw new \InvalidArgumentException("illegal character encountered: " . substr((string)$stringRepresentation, $pos));
            }
        }

        // no EOL at end?
        if ($bitsPos > $rowStartPos) {
            if ($rowLength == -1) {
                $rowLength = $bitsPos - $rowStartPos;
            } elseif ($bitsPos - $rowStartPos != $rowLength) {
                throw new \InvalidArgumentException("row lengths do not match");
            }
            $nRows++;
        }

        $matrix = new BitMatrix($rowLength, $nRows);
        for ($i = 0; $i < $bitsPos; $i++) {
            if ($bits[$i]) {
                $matrix->set($i % $rowLength, $i / $rowLength);
            }
        }

        return $matrix;
    }

    /**
     * <p>Sets the given bit to true.</p>
     *
     * @param float|int $x
     * @param float|int $y
     */
    public function set(int|float $x, int|float $y): void
    {
        $offset = (int)($y * $this->rowSize + ($x / 32));
        if (!isset($this->bits[$offset])) {
            $this->bits[$offset] = 0;
        }

        $bob                 = $this->bits[$offset];
        $bob                 |= 1 << ($x & 0x1f);
        $this->bits[$offset] |= ($bob);
    }

    public function _unset($x, $y): void
    {
//было unset, php не позволяет использовать unset
        $offset              = (int)($y * $this->rowSize + ($x / 32));
        $this->bits[$offset] &= ~(1 << ($x & 0x1f));
    }

    /**1 << (249 & 0x1f)
     * <p>Flips the given bit.</p>
     *
     * @param $x ;  The horizontal component (i.e. which column)
     * @param $y ;  The vertical component (i.e. which row)
     */
    /**
     * @psalm-param 0|positive-int $x
     * @psalm-param 0|positive-int $y
     */
    public function flip(int $x, int $y): void
    {
        $offset = $y * $this->rowSize + (int)($x / 32);

        $this->bits[$offset] = ($this->bits[$offset] ^ (1 << ($x & 0x1f)));
    }

    /**
     * Exclusive-or (XOR): Flip the bit in this {@code BitMatrix} if the corresponding
     * mask bit is set.
     *
     * @param $mask ;  XOR mask
     */
    public function _xor($mask): void
    {
//было xor, php не позволяет использовать xor
        if ($this->width != $mask->getWidth() || $this->height != $mask->getHeight() || $this->rowSize != $mask->getRowSize()) {
            throw new \InvalidArgumentException("input matrix dimensions do not match");
        }
        $rowArray = new BitArray($this->width / 32 + 1);
        for ($y = 0; $y < $this->height; $y++) {
            $offset = $y * $this->rowSize;
            $row    = $mask->getRow($y, $rowArray)->getBitArray();
            for ($x = 0; $x < $this->rowSize; $x++) {
                $this->bits[$offset + $x] ^= $row[$x];
            }
        }
    }

    /**
     * Clears all bits (sets to false).
     */
    public function clear(): void
    {
        $max = is_countable($this->bits) ? count($this->bits) : 0;
        for ($i = 0; $i < $max; $i++) {
            $this->bits[$i] = 0;
        }
    }

    /**
     * <p>Sets a square region of the bit matrix to true.</p>
     *
     * @param $left   ;  The horizontal position to begin at (inclusive)
     * @param $top    ;  The vertical position to begin at (inclusive)
     * @param $width  ;  The width of the region
     * @param $height ;  The height of the region
     *
     * @psalm-param 0|6|9 $left
     * @psalm-param 0|6|9 $top
     */
    public function setRegion(int $left, int $top, int $width, int $height): void
    {
        if ($top < 0 || $left < 0) {
            throw new \InvalidArgumentException("Left and top must be nonnegative");
        }
        if ($height < 1 || $width < 1) {
            throw new \InvalidArgumentException("Height and width must be at least 1");
        }
        $right  = $left + $width;
        $bottom = $top + $height;
        if ($bottom > $this->height || $right > $this->width) { //> this.height || right > this.width
            throw new \InvalidArgumentException("The region must fit inside the matrix");
        }
        for ($y = $top; $y < $bottom; $y++) {
            $offset = $y * $this->rowSize;
            for ($x = $left; $x < $right; $x++) {
                $this->bits[$offset + (int)($x / 32)] = ($this->bits[$offset + (int)($x / 32)] |= 1 << ($x & 0x1f));
            }
        }
    }

    /**
     * Modifies this {@code BitMatrix} to represent the same but rotated 180 degrees
     */
    public function rotate180(): void
    {
        $width     = $this->getWidth();
        $height    = $this->getHeight();
        $topRow    = new BitArray($width);
        $bottomRow = new BitArray($width);
        for ($i = 0; $i < ($height + 1) / 2; $i++) {
            $topRow    = $this->getRow($i, $topRow);
            $bottomRow = $this->getRow($height - 1 - $i, $bottomRow);
            $topRow->reverse();
            $bottomRow->reverse();
            $this->setRow($i, $bottomRow);
            $this->setRow($height - 1 - $i, $topRow);
        }
    }

    /**
     * @return float The width of the matrix
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * A fast method to retrieve one row of data from the matrix as a BitArray.
     *
     * @param float|int $y
     *
     * @param BitArray  $row ;  An optional caller-allocated BitArray, will be allocated if null or too small
     *
     * @return BitArray
     */
    public function getRow(int|float $y, BitArray $row): BitArray
    {
        if ($row == null || $row->getSize() < $this->width) {
            $row = new BitArray($this->width);
        } else {
            $row->clear();
        }
        $offset = $y * $this->rowSize;
        for ($x = 0; $x < $this->rowSize; $x++) {
            $row->setBulk($x * 32, $this->bits[$offset + $x]);
        }

        return $row;
    }

    /**
     * @param float|int $y
     *
     * @param BitArray  $row ;  {@link BitArray} to copy from
     */
    public function setRow(int|float $y, BitArray $row): void
    {
        $this->bits = arraycopy($row->getBitArray(), 0, $this->bits, $y * $this->rowSize, $this->rowSize);
    }

    /**
     * This is useful in detecting the enclosing rectangle of a 'pure' barcode.
     *
     * @return (int|mixed)[]|null
     *
     * @psalm-return array{0: int|mixed, 1: 0|mixed|positive-int, 2: mixed, 3: mixed}|null
     */
    public function getEnclosingRectangle(): array|null
    {
        $left   = $this->width;
        $top    = $this->height;
        $right  = -1;
        $bottom = -1;

        for ($y = 0; $y < $this->height; $y++) {
            for ($x32 = 0; $x32 < $this->rowSize; $x32++) {
                $theBits = $this->bits[$y * $this->rowSize + $x32];
                if ($theBits != 0) {
                    if ($y < $top) {
                        $top = $y;
                    }
                    if ($y > $bottom) {
                        $bottom = $y;
                    }
                    if ($x32 * 32 < $left) {
                        $bit = 0;
                        while (($theBits << (31 - $bit)) == 0) {
                            $bit++;
                        }
                        if (($x32 * 32 + $bit) < $left) {
                            $left = $x32 * 32 + $bit;
                        }
                    }
                    if ($x32 * 32 + 31 > $right) {
                        $bit = 31;
                        while ((sdvig3($theBits, $bit)) == 0) {//>>>
                            $bit--;
                        }
                        if (($x32 * 32 + $bit) > $right) {
                            $right = $x32 * 32 + $bit;
                        }
                    }
                }
            }
        }

        $width  = $right - $left;
        $height = $bottom - $top;

        if ($width < 0 || $height < 0) {
            return null;
        }

        return [
            $left,
            $top,
            $width,
            $height,
        ];
    }

    /**
     * This is useful in detecting a corner of a 'pure' barcode.
     *
     * @psalm-return array{0: mixed, 1: mixed}|null
     */
    public function getTopLeftOnBit(): array|null
    {
        $bitsOffset = 0;
        while ($bitsOffset < (is_countable($this->bits) ? count($this->bits) : 0) && $this->bits[$bitsOffset] == 0) {
            $bitsOffset++;
        }
        if ($bitsOffset == (is_countable($this->bits) ? count($this->bits) : 0)) {
            return null;
        }
        $y = $bitsOffset / $this->rowSize;
        $x = ($bitsOffset % $this->rowSize) * 32;

        $theBits = $this->bits[$bitsOffset];
        $bit     = 0;
        while (($theBits << (31 - $bit)) == 0) {
            $bit++;
        }
        $x += $bit;

        return [
            $x,
            $y,
        ];
    }

    /**
     * @psalm-return array{0: mixed, 1: mixed}|null
     */
    public function getBottomRightOnBit(): array|null
    {
        $bitsOffset = (is_countable($this->bits) ? count($this->bits) : 0) - 1;
        while ($bitsOffset >= 0 && $this->bits[$bitsOffset] == 0) {
            $bitsOffset--;
        }
        if ($bitsOffset < 0) {
            return null;
        }

        $y = $bitsOffset / $this->rowSize;
        $x = ($bitsOffset % $this->rowSize) * 32;

        $theBits = $this->bits[$bitsOffset];
        $bit     = 31;
        while ((sdvig3($theBits, $bit)) == 0) {//>>>
            $bit--;
        }
        $x += $bit;

        return [
            $x,
            $y,
        ];
    }

    /**
     * @return float The height of the matrix
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return int The row size of the matrix
     */
    public function getRowSize()
    {
        return $this->rowSize;
    }

    public function equals($o): bool
    {
        if (!($o instanceof BitMatrix)) {
            return false;
        }
        $other = $o;

        return $this->width == $other->width && $this->height == $other->height && $this->rowSize == $other->rowSize && $this->bits === $other->bits;
    }


    public function hashCode(): float|int
    {
        $hash = $this->width;
        $hash = 31 * $hash + $this->width;
        $hash = 31 * $hash + $this->height;
        $hash = 31 * $hash + $this->rowSize;
        $hash = 31 * $hash + hashCode($this->bits);

        return $hash;
    }


    public function toString($setString = '', $unsetString = '', $lineSeparator = ''): string
    {
        if (!$setString || !$unsetString) {
            return (string)'X ' . '  ';
        }
        if ($lineSeparator && $lineSeparator !== "\n") {
            return $this->toString_($setString, $unsetString, $lineSeparator);
        }

        return (string)($setString . $unsetString . "\n");
    }

    public function toString_($setString, $unsetString, $lineSeparator): string
    {
        //$result = new StringBuilder(height * (width + 1));
        $result = '';
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $result .= ($this->get($x, $y) ? $setString : $unsetString);
            }
            $result .= ($lineSeparator);
        }

        return (string)$result;
    }

    /**
     * @deprecated call {@link #toString(String,String)} only, which uses \n line separator always
     */
    // @Deprecated
    /**
     * <p>Gets the requested bit, where true means black.</p>
     *
     * @param $x ;  The horizontal component (i.e. which column)
     * @param $y ;  The vertical component (i.e. which row)
     *
     * @return bool of given bit in matrix
     */
    public function get(int $x, int $y): bool
    {
        $offset = (int)($y * $this->rowSize + ($x / 32));
        if (!isset($this->bits[$offset])) {
            $this->bits[$offset] = 0;
        }

        // return (($this->bits[$offset] >> ($x & 0x1f)) & 1) != 0;
        //было >>> вместо >>, не знаю как эмулировать беззнаковый сдвиг
        return (uRShift($this->bits[$offset], ($x & 0x1f)) & 1) != 0;
    }

    //  @Override

    public function _clone(): BitMatrix
    {
        return new BitMatrix($this->width, $this->height, $this->rowSize, $this->bits);
    }
}
