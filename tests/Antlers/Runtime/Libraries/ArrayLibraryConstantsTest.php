<?php

namespace Tests\Antlers\Runtime\Libraries;

use Tests\Antlers\ParserTestCase;

class ArrayLibraryConstantsTest extends ParserTestCase
{
    public function test_array_constants_php_compatibility()
    {
        $this->assertSame(SORT_REGULAR, $this->evaluateRaw('arr.SORT_REGULAR()'));
        $this->assertSame(SORT_NUMERIC, $this->evaluateRaw('arr.SORT_NUMERIC()'));
        $this->assertSame(SORT_STRING, $this->evaluateRaw('arr.SORT_STRING()'));
        $this->assertSame(SORT_LOCALE_STRING, $this->evaluateRaw('arr.SORT_LOCALE_STRING()'));
        $this->assertSame(SORT_NATURAL, $this->evaluateRaw('arr.SORT_NATURAL()'));
        $this->assertSame(SORT_FLAG_CASE, $this->evaluateRaw('arr.SORT_FLAG_CASE()'));
        $this->assertSame(COUNT_NORMAL, $this->evaluateRaw('arr.COUNT_NORMAL()'));
        $this->assertSame(COUNT_RECURSIVE, $this->evaluateRaw('arr.COUNT_RECURSIVE()'));
    }

    public function test_combining_array_constants_with_bitwise_or()
    {
        $this->assertSame(
            SORT_FLAG_CASE | SORT_NATURAL,
            $this->evaluateRaw('arr.SORT_FLAG_CASE() bwo array.SORT_NATURAL()')
        );
    }
}
