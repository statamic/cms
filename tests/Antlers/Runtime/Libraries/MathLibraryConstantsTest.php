<?php

namespace Tests\Antlers\Runtime\Libraries;

use Tests\Antlers\ParserTestCase;

class MathLibraryConstantsTest extends ParserTestCase
{
    public function test_constant_php_compatibility()
    {
        $this->assertSame(M_PI, $this->evaluateRaw('math.M_PI()'));
        $this->assertSame(M_E, $this->evaluateRaw('math.M_E()'));
        $this->assertSame(M_LOG2E, $this->evaluateRaw('math.M_LOG2E()'));
        $this->assertSame(M_LOG10E, $this->evaluateRaw('math.M_LOG10E()'));
        $this->assertSame(M_LN2, $this->evaluateRaw('math.M_LN2()'));
        $this->assertSame(M_LN10, $this->evaluateRaw('math.M_LN10()'));
        $this->assertSame(M_PI_2, $this->evaluateRaw('math.M_PI_2()'));
        $this->assertSame(M_PI_4, $this->evaluateRaw('math.M_PI_4()'));
        $this->assertSame(M_1_PI, $this->evaluateRaw('math.M_1_PI()'));
        $this->assertSame(M_2_PI, $this->evaluateRaw('math.M_2_PI()'));
        $this->assertSame(M_SQRTPI, $this->evaluateRaw('math.M_SQRTPI()'));
        $this->assertSame(M_2_SQRTPI, $this->evaluateRaw('math.M_2_SQRTPI()'));
        $this->assertSame(M_SQRT2, $this->evaluateRaw('math.M_SQRT2()'));
        $this->assertSame(M_SQRT3, $this->evaluateRaw('math.M_SQRT3()'));
        $this->assertSame(M_SQRT1_2, $this->evaluateRaw('math.M_SQRT1_2()'));
        $this->assertSame(M_LNPI, $this->evaluateRaw('math.M_LNPI()'));
        $this->assertSame(M_EULER, $this->evaluateRaw('math.M_EULER()'));
        $this->assertSame(INF, $this->evaluateRaw('math.M_INF()'));
    }
}
