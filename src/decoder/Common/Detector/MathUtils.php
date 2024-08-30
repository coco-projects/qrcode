<?php

    namespace Coco\qrcode\decoder\Common\Detector;

final class MathUtils
{
    private function __construct()
    {
    }

    /**
     * Ends up being a bit faster than {@link Math#round(float)}. This merely rounds its
     * argument to the nearest int, where x.5 rounds up to x+1. Semantics of this shortcut
     * differ slightly from {@link Math#round(float)} in that half rounds down for negative
     * values. -2.5 rounds to -3, not -2. For purposes here it makes no difference.
     *
     * @param float $d real value to round
     *
     * @return int {@code int}
     */
    public static function round(float $d): int
    {
        return (int)($d + ($d < 0.0 ? -0.5 : 0.5));
    }

    public static function distance(float|int $aX, float|int $aY, float $bX, float $bY): float
    {
        $xDiff = $aX - $bX;
        $yDiff = $aY - $bY;

        return (float)sqrt($xDiff * $xDiff + $yDiff * $yDiff);
    }
}
