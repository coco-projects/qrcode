<?php

    namespace Coco\qrcode\decoder\Common\Reedsolomon;

    /**
     * <p>This class contains utility methods for performing mathematical operations over
     * the Galois Fields. Operations use a given primitive polynomial in calculations.</p>
     *
     * <p>Throughout this package, elements of the GF are represented as an {@code int}
     * for convenience and speed (but at the cost of memory).
     * </p>
     *
     * @author Sean Owen
     * @author David Olivier
     */
final class GenericGF
{
    public static $AZTEC_DATA_12;
    public static $AZTEC_DATA_10;
    public static $AZTEC_DATA_6;
    public static $AZTEC_PARAM;
    public static $QR_CODE_FIELD_256;
    public static $DATA_MATRIX_FIELD_256;
    public static $AZTEC_DATA_8;
    public static $MAXICODE_FIELD_64;

    private array         $expTable = [];
    private array         $logTable = [];
    private GenericGFPoly $zero;
    private GenericGFPoly $one;

    /**
     * Create a representation of GF(size) using the given primitive polynomial.
     *
     * @param int $primitive     irreducible polynomial whose coefficients are represented by
     *                           the bits of an int, where the least-significant bit represents the constant
     *                           coefficient
     * @param int $size          the size of the field
     * @param int $generatorBase the factor b in the generator polynomial can be 0- or 1-based
     *                           (g(x) = (x+a^b)(x+a^(b+1))...(x+a^(b+2t-1))).
     *                           In most cases it should be 1, but for QR code it is 0.
     */
    public function __construct(private $primitive, private $size, private $generatorBase)
    {
        $x = 1;
        for ($i = 0; $i < $size; $i++) {
            $this->expTable[$i] = $x;
            $x                  *= 2; // we're assuming the generator alpha is 2
            if ($x >= $size) {
                $x ^= $primitive;
                $x &= $size - 1;
            }
        }
        for ($i = 0; $i < $size - 1; $i++) {
            $this->logTable[$this->expTable[$i]] = $i;
        }
        // logTable[0] == 0 but this should never be used
        $this->zero = new GenericGFPoly($this, [0]);
        $this->one  = new GenericGFPoly($this, [1]);
    }

    public static function Init(): void
    {
        self::$AZTEC_DATA_12         = new GenericGF(0x1069, 4096, 1); // x^12 + x^6 + x^5 + x^3 + 1
        self::$AZTEC_DATA_10         = new GenericGF(0x409, 1024, 1);  // x^10 + x^3 + 1
        self::$AZTEC_DATA_6          = new GenericGF(0x43, 64, 1);     // x^6 + x + 1
        self::$AZTEC_PARAM           = new GenericGF(0x13, 16, 1);     // x^4 + x + 1
        self::$QR_CODE_FIELD_256     = new GenericGF(0x011D, 256, 0);  // x^8 + x^4 + x^3 + x^2 + 1
        self::$DATA_MATRIX_FIELD_256 = new GenericGF(0x012D, 256, 1);  // x^8 + x^5 + x^3 + x^2 + 1
        self::$AZTEC_DATA_8          = self::$DATA_MATRIX_FIELD_256;
        self::$MAXICODE_FIELD_64     = self::$AZTEC_DATA_6;
    }

    /**
     * Implements both addition and subtraction -- they are the same in GF(size).
     *
     * @param float|int|null $b
     *
     * @return float|int sum/difference of a and b
     *
     */
    public static function addOrSubtract(int $a, int|float|null $b)
    {
        return $a ^ $b;
    }

    public function getZero(): GenericGFPoly
    {
        return $this->zero;
    }

    public function getOne(): GenericGFPoly
    {
        return $this->one;
    }

    /**
     * @return GenericGFPoly  the monomial representing coefficient * x^degree
     */
    public function buildMonomial($degree, int $coefficient)
    {
        if ($degree < 0) {
            throw new \InvalidArgumentException();
        }
        if ($coefficient == 0) {
            return $this->zero;
        }
        $coefficients    = fill_array(0, $degree + 1, 0);//new int[degree + 1];
        $coefficients[0] = $coefficient;

        return new GenericGFPoly($this, $coefficients);
    }

    /**
     * @return 2 to the power of a in GF(size)
     */
    public function exp($a)
    {
        return $this->expTable[$a];
    }

    /**
     * @return float base 2 log of a in GF(size)
     */
    public function log(float|int|null $a)
    {
        if ($a == 0) {
            throw new \InvalidArgumentException();
        }

        return $this->logTable[$a];
    }

    /**
     * @return float multiplicative inverse of a
     */
    public function inverse($a)
    {
        if ($a == 0) {
            throw new \Exception();
        }

        return $this->expTable[$this->size - $this->logTable[$a] - 1];
    }

    /**
     * @param float|int|null $b
     * @param float|int|null $a
     *
     * @return int product of a and b in GF(size)
     *
     */
    public function multiply(int|float|null $a, int|float|null $b): int
    {
        if ($a == 0 || $b == 0) {
            return 0;
        }

        return $this->expTable[($this->logTable[$a] + $this->logTable[$b]) % ($this->size - 1)];
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getGeneratorBase(): int
    {
        return $this->generatorBase;
    }

    // @Override
    public function toString(): string
    {
        return "GF(0x" . dechex((int)($this->primitive)) . ',' . $this->size . ')';
    }
}

    GenericGF::Init();
