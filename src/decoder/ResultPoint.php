<?php

    namespace Coco\qrcode\decoder;

    use Coco\qrcode\decoder\Common\Detector\MathUtils;

    /**
     * <p>Encapsulates a point of interest in an image containing a barcode. Typically, this
     * would be the location of a finder pattern or the corner of the barcode, for example.</p>
     *
     * @author Sean Owen
     */
class ResultPoint
{
    private float $x;
    private float $y;

    public function __construct($x, $y)
    {
        $this->x = (float)($x);
        $this->y = (float)($y);
    }

    /**
     * Orders an array of three ResultPoints in an order [A,B,C] such that AB is less than AC
     * and BC is less than AC, and the angle between BC and BA is less than 180 degrees.
     *
     * @param array $patterns of three {@code ResultPoint} to order
     */
    public static function orderBestPatterns(array $patterns): array
    {

// Find distances between pattern centers
        $zeroOneDistance = self::distance($patterns[0], $patterns[1]);
        $oneTwoDistance  = self::distance($patterns[1], $patterns[2]);
        $zeroTwoDistance = self::distance($patterns[0], $patterns[2]);

        $pointA = '';
        $pointB = '';
        $pointC = '';
        // Assume one closest to other two is B; A and C will just be guesses at first
        if ($oneTwoDistance >= $zeroOneDistance && $oneTwoDistance >= $zeroTwoDistance) {
            $pointB = $patterns[0];
            $pointA = $patterns[1];
            $pointC = $patterns[2];
        } elseif ($zeroTwoDistance >= $oneTwoDistance && $zeroTwoDistance >= $zeroOneDistance) {
            $pointB = $patterns[1];
            $pointA = $patterns[0];
            $pointC = $patterns[2];
        } else {
            $pointB = $patterns[2];
            $pointA = $patterns[0];
            $pointC = $patterns[1];
        }

        // Use cross product to figure out whether A and C are correct or flipped.
        // This asks whether BC x BA has a positive z component, which is the arrangement
        // we want for A, B, C. If it's negative, then we've got it flipped around and
        // should swap A and C.
        if (self::crossProductZ($pointA, $pointB, $pointC) < 0.0) {
            $temp   = $pointA;
            $pointA = $pointC;
            $pointC = $temp;
        }

        $patterns[0] = $pointA;
        $patterns[1] = $pointB;
        $patterns[2] = $pointC;

        return $patterns;
    }

    /**
     * @param ResultPoint $pattern1 first pattern
     * @param ResultPoint $pattern2 second pattern
     *
     * @return float distance between two points
     */
    public static function distance($pattern1, $pattern2)
    {
        return MathUtils::distance($pattern1->x, $pattern1->y, $pattern2->x, $pattern2->y);
    }


    /**
     * Returns the z component of the cross product between vectors BC and BA.
     */
    private static function crossProductZ($pointA, $pointB, $pointC)
    {
        $bX = $pointB->x;
        $bY = $pointB->y;

        return (($pointC->x - $bX) * ($pointA->y - $bY)) - (($pointC->y - $bY) * ($pointA->x - $bX));
    }


    final public function getX(): float
    {
        return (float)($this->x);
    }


    final public function getY(): float
    {
        return (float)($this->y);
    }

    final public function equals($other): bool
    {
        if ($other instanceof ResultPoint) {
            $otherPoint = $other;

            return $this->x == $otherPoint->x && $this->y == $otherPoint->y;
        }

        return false;
    }

    final public function hashCode()
    {
        return 31 * floatToIntBits($this->x) + floatToIntBits($this->y);
    }

    final public function toString(): string
    {
        $result = '';
        $result .= ('(');
        $result .= ($this->x);
        $result .= (',');
        $result .= ($this->y);
        $result .= (')');

        return $result;
    }
}
