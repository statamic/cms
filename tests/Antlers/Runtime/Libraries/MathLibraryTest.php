<?php

namespace Tests\Antlers\Runtime\Libraries;

use Tests\Antlers\ParserTestCase;

class MathLibraryTest extends ParserTestCase
{
    protected $tests = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->tests = [
            [abs(-10), 'math.abs(-10)', 'abs'],
            [acos(-1), 'math.acos(-1)', 'acos'],
            [acosh(1), 'math.acosh(1)', 'acosh'],
            [asinh(1), 'math.asinh(1)', 'asinh'],
            [atan2(1, 2), 'math.atan2(1, 2)', 'atan2'],
            [atan(1), 'math.atan(1)', 'atan'],
            [atanh(1), 'math.atanh(1)', 'atanh'],
            [base_convert('a37334', 16, 2), 'math.baseConvert("a37334", 16, 2)', 'baseConvert'],
            [bindec('110011'), 'math.bindec("110011")', 'bindec'],
            [ceil(4.3), 'math.ceil(4.3)', 'ceil'],
            [cos(M_PI), 'math.cos(math.M_PI())', 'cos'],
            [cosh(2), 'math.cosh(2)', 'cosh'],
            [decbin(12), 'math.decbin(12)', 'decbin'],
            [dechex(10), 'math.dechex(10)', 'dechex'],
            [decoct(15), 'math.decoct(15)', 'decoct'],
            [deg2rad(45), 'math.deg2rad(45)', 'deg2rad'],
            [exp(12), 'math.exp(12)', 'exp'],
            [expm1(12), 'math.expm1(12)', 'expm1'],
            [fdiv(5.7, 1.3), 'math.fdiv(5.7, 1.3)', 'fdiv'],
            [floor(4.3), 'math.floor(4.3)', 'floor'],
            [fmod(5.7, 1.3), 'math.fmod(5.7, 1.3)', 'fmod'],
            [getrandmax(), 'math.randMax()', 'randMax'],
            [hexdec('1e'), 'math.hexdec("1e")', 'hexdec'],
            [hypot(3, 4), 'math.hypot(3, 4)', 'hypot'],
            [intdiv(3, 2), 'math.intdiv(3, 2)', 'intdiv'],
            [is_finite(42), 'math.isFinite(42)', 'isFinite'],
            [is_finite(INF), 'math.isFinite(math.M_INF())', 'isFinite'],
            [is_infinite(INF), 'math.isInfinite(math.M_INF())', 'isInfinite'],
            [is_infinite(42), 'math.isInfinite(42)', 'isInfinite'],
            [is_nan(null), 'math.isNan(null)', 'isNan'],
            [log10(4), 'math.log10(4)', 'log10'],
            [log1p(42), 'math.log1p(42)', 'log1p'],
            [log(10), 'math.log(10)', 'log'],
            [octdec(77), 'math.octdec("77")', 'octdec'],
            [pow(10, -1), 'math.pow(10, -1)', 'pow'],
            [rad2deg(M_PI_4), 'math.rad2deg(math.M_PI_4())', 'rad2deg'],
            [round(3.4), 'math.round(3.4)', 'round'],
            [sinh(32), 'math.sinh(32)', 'sinh'],
            [sqrt(16), 'math.sqrt(16)', 'sqrt'],
            [tan(M_PI_4), 'math.tan(math.M_PI_4())', 'tan'],
            [tanh(42), 'math.tanh(42)', 'tanh'],
            [min(32, 41, 424, 1), 'math.min(32,41,424,1)', 'min'],
            [max(32, 41, 424, 1), 'math.max(32,41,424,1)', 'max'],
            [120, 'math.factorial(5)', 'factorial'],
        ];
    }

    public function test_math_library_functions()
    {
        foreach ($this->tests as $test) {
            $expected = $test[0];
            $toRender = $test[1];
            $message = $test[2];

            $this->assertSame((string) $expected, $this->renderLibraryMethod($toRender), 'lib math.'.$message);
        }
    }
}
