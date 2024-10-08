<?php

if (!function_exists('arraycopy')) {
    function arraycopy($srcArray, $srcPos, $destArray, $destPos, $length): array
    {
        $srcArrayToCopy = array_slice($srcArray, $srcPos, $length);
        array_splice($destArray, $destPos, $length, $srcArrayToCopy);

        return $destArray;
    }
}

if (!function_exists('hashCode')) {
    function hashCode($s): int
    {
        $h   = 0;
        $len = strlen((string)$s);
        for ($i = 0; $i < $len; $i++) {
            $h = (31 * $h + ord($s[$i]));
        }

        return $h;
    }
}

if (!function_exists('numberOfTrailingZeros')) {
    /**
     * @psalm-return 0|32|positive-int
     */
    function numberOfTrailingZeros($i): int
    {
        if ($i == 0) {
            return 32;
        }
        $num = 0;
        while (($i & 1) == 0) {
            $i >>= 1;
            $num++;
        }

        return $num;
    }
}

if (!function_exists('uRShift')) {
    function uRShift($a, $b)
    {
        static $mask = (8 * PHP_INT_SIZE - 1);
        if ($b === 0) {
            return $a;
        }

        return ($a >> $b) & ~(1 << $mask >> ($b - 1));
    }
}

if (!function_exists('sdvig3')) {
    function sdvig3($a, $b): float|int
    {
        if ($a >= 0) {
            return bindec(decbin($a >> $b)); //simply right shift for positive number
        }

        $bin = decbin($a >> $b);

        $bin = substr($bin, $b); // zero fill on the left side

        return bindec($bin);
    }
}

if (!function_exists('floatToIntBits')) {
    function floatToIntBits($float_val)
    {
        $int = unpack('i', pack('f', $float_val));

        return $int[1];
    }
}

if (!function_exists('fill_array')) {
    /**
     * @psalm-return array<int, mixed>
     */
    function fill_array($index, $count, $value): array
    {
        if ($count <= 0) {
            return [0];
        }

        return array_fill($index, $count, $value);
    }
}
