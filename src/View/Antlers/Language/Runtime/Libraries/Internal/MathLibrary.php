<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;
use Statamic\View\Antlers\Language\Runtime\Sandbox\RuntimeHelpers;

class MathLibrary extends RuntimeLibrary
{
    protected $name = 'math';

    protected $exposedMethods = [];

    public function __construct()
    {
        $this->exposedMethods = [
            'M_PI' => 1, 'M_E' => 1, 'M_LOG2E' => 1,
            'M_LOG10E' => 1, 'M_LN2' => 1, 'M_LN10' => 1,
            'M_PI_2' => 1, 'M_PI_4' => 1, 'M_1_PI' => 1,
            'M_2_PI' => 1, 'M_SQRTPI' => 1, 'M_2_SQRTPI' => 1,
            'M_SQRT2' => 1, 'M_SQRT3' => 1, 'M_SQRT1_2' => 1,
            'M_LNPI' => 1, 'M_EULER' => 1, 'M_INF' => 1, 'M_NAN' => 1,
            'ROUND_HALF_UP' => 1, 'ROUND_HALF_DOWN' => 1,
            'ROUND_HALF_EVEN' => 1, 'ROUND_HALF_ODD' => 1,
            'abs' => [$this->numericVar('num')],
            'acos' => [$this->numericVar('num')],
            'acosh' => [$this->numericVar('num')],
            'asinh' => [$this->numericVar('num')],
            'atan2' => [
                $this->numericVar('y'),
                $this->numericVar('x'),
            ],
            'atan' => [$this->numericVar('num')],
            'atanh' => [$this->numericVar('num')],
            'baseConvert' => [
                $this->stringVar('num'),
                $this->numericVar('from_base'),
                $this->numericVar('to_base'),
            ],
            'bindec' => [$this->stringVar('binary_string')],
            'ceil' => [$this->numericVar('num')],
            'cos' => [$this->numericVar('num')],
            'cosh' => [$this->numericVar('num')],
            'decbin' => [$this->numericVar('num')],
            'dechex' => [$this->numericVar('num')],
            'decoct' => [$this->numericVar('num')],
            'deg2rad' => [$this->numericVar('num')],
            'exp' => [$this->numericVar('num')],
            'expm1' => [$this->numericVar('num')],
            'fdiv' => [
                $this->numericVar('num1'),
                $this->numericVar('num2'),
            ],
            'floor' => [$this->numericVar('num')],
            'fmod' => [
                $this->numericVar('num1'),
                $this->numericVar('num2'),
            ],
            'randMax' => 1,
            'hexdec' => [$this->stringVar('hex_string')],
            'hypot' => [
                $this->numericVar('x'),
                $this->numericVar('y'),
            ],
            'intdiv' => [
                $this->numericVar('num1'),
                $this->numericVar('num2'),
            ],
            'isFinite' => [$this->numericVar('num')],
            'isInfinite' => [$this->numericVar('num')],
            'isNan' => [$this->numericVar('num')],
            'log10' => [$this->numericVar('num')],
            'log1p' => [$this->numericVar('num')],
            'log' => [
                $this->numericVar('num'),
                $this->numericVarWithDefault('base', M_E),
            ],
            'octdec' => [$this->stringVar('octal_string')],
            'pow' => [
                $this->numericVar('base'),
                $this->numericVar('exp'),
            ],
            'rad2deg' => [
                $this->numericVar('num'),
            ],
            'rand' => [
                $this->numericVarWithDefault('min', 0),
                $this->numericVarWithDefault('max', null),
            ],
            'round' => [
                $this->numericVar('num'),
                $this->numericVarWithDefault('precision', 0),
                $this->numericVarWithDefault('mode', PHP_ROUND_HALF_UP),
            ],
            'sinh' => [$this->numericVar('num')],
            'sqrt' => [$this->numericVar('num')],
            'tan' => [$this->numericVar('num')],
            'tanh' => [$this->numericVar('num')],
            'max' => [
                [
                    self::KEY_NAME => 'nums',
                    self::KEY_ACCEPTS => [self::KEY_TYPE_NUMERIC],
                    self::KEY_ACCEPTS_MANY => true,
                ],
            ],
            'min' => [
                [
                    self::KEY_NAME => 'nums',
                    self::KEY_ACCEPTS => [self::KEY_TYPE_NUMERIC],
                    self::KEY_ACCEPTS_MANY => true,
                ],
            ],
            'factorial' => [$this->numericVar('num')],
        ];
    }

    public function M_PI()
    {
        return M_PI;
    }

    public function M_E()
    {
        return M_E;
    }

    public function M_LOG2E()
    {
        return M_LOG2E;
    }

    public function M_LOG10E()
    {
        return M_LOG10E;
    }

    public function M_LN2()
    {
        return M_LN2;
    }

    public function M_LN10()
    {
        return M_LN10;
    }

    public function M_PI_2()
    {
        return M_PI_2;
    }

    public function M_PI_4()
    {
        return M_PI_4;
    }

    public function M_1_PI()
    {
        return M_1_PI;
    }

    public function M_2_PI()
    {
        return M_2_PI;
    }

    public function M_SQRTPI()
    {
        return M_SQRTPI;
    }

    public function M_2_SQRTPI()
    {
        return M_2_SQRTPI;
    }

    public function M_SQRT2()
    {
        return M_SQRT2;
    }

    public function M_SQRT3()
    {
        return M_SQRT3;
    }

    public function M_SQRT1_2()
    {
        return M_SQRT1_2;
    }

    public function M_LNPI()
    {
        return M_LNPI;
    }

    public function M_EULER()
    {
        return M_EULER;
    }

    public function M_INF()
    {
        return INF;
    }

    public function M_NAN()
    {
        return NAN;
    }

    public function ROUND_HALF_UP()
    {
        return PHP_ROUND_HALF_UP;
    }

    public function ROUND_HALF_DOWN()
    {
        return PHP_ROUND_HALF_DOWN;
    }

    public function ROUND_HALF_EVEN()
    {
        return PHP_ROUND_HALF_EVEN;
    }

    public function ROUND_HALF_ODD()
    {
        return PHP_ROUND_HALF_ODD;
    }

    public function factorial($num)
    {
        return RuntimeHelpers::factorial($num);
    }

    public function abs($value)
    {
        return abs($value);
    }

    public function acos($value)
    {
        return acos($value);
    }

    public function acosh($value)
    {
        return acosh($value);
    }

    public function asinh($value)
    {
        return asinh($value);
    }

    public function atan2($x, $y)
    {
        return atan2($x, $y);
    }

    public function atan($value)
    {
        return atan($value);
    }

    public function atanh($value)
    {
        return atanh($value);
    }

    public function baseConvert($num, $from_base, $to_base)
    {
        return base_convert($num, $from_base, $to_base);
    }

    public function bindec($binary_string)
    {
        return bindec($binary_string);
    }

    public function ceil($num)
    {
        return ceil($num);
    }

    public function cos($num)
    {
        return cos($num);
    }

    public function cosh($num)
    {
        return cosh($num);
    }

    public function decbin($num)
    {
        return decbin($num);
    }

    public function dechex($num)
    {
        return dechex($num);
    }

    public function decoct($num)
    {
        return decoct($num);
    }

    public function deg2rad($num)
    {
        return deg2rad($num);
    }

    public function exp($num)
    {
        return exp($num);
    }

    public function expm1($num)
    {
        return expm1($num);
    }

    public function fdiv($num1, $num2)
    {
        return fdiv($num1, $num2);
    }

    public function floor($num)
    {
        return floor($num);
    }

    public function fmod($num1, $num2)
    {
        return fmod($num1, $num2);
    }

    public function randMax()
    {
        return getrandmax();
    }

    public function hexdec($hex_string)
    {
        return hexdec($hex_string);
    }

    public function hypot($x, $y)
    {
        return hypot($x, $y);
    }

    public function intdiv($num1, $num2)
    {
        return intdiv($num1, $num2);
    }

    public function isFinite($num)
    {
        return is_finite($num);
    }

    public function isInfinite($num)
    {
        return is_infinite($num);
    }

    public function isNan($num)
    {
        return is_nan($num);
    }

    public function log10($num)
    {
        return log10($num);
    }

    public function log1p($num)
    {
        return log1p($num);
    }

    public function log($num, $base = M_E)
    {
        return log($num, $base = M_E);
    }

    public function octdec($octal_string)
    {
        return octdec($octal_string);
    }

    public function pow($base, $exp)
    {
        return pow($base, $exp);
    }

    public function rad2deg($num)
    {
        return rad2deg($num);
    }

    public function rand($min = 0, $max = null)
    {
        return rand($min, $max);
    }

    public function round($num, $precision = 0, $mode = PHP_ROUND_HALF_UP)
    {
        return round($num, $precision, $mode);
    }

    public function sinh($num)
    {
        return sinh($num);
    }

    public function sqrt($num)
    {
        return sqrt($num);
    }

    public function tan($num)
    {
        return tan($num);
    }

    public function tanh($num)
    {
        return tanh($num);
    }

    public function max($nums)
    {
        return max($nums);
    }

    public function min($nums)
    {
        return min($nums);
    }
}
