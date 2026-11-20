<?php

namespace Tests\Antlers\Parser;

use PHPUnit\Framework\TestCase;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class StringUtilitiesTest extends TestCase
{
    public function test_encoding_detection_selects_multibyte_operations()
    {
        StringUtilities::prepareSplit("caf\u{00E9}");

        $this->assertSame(StringUtilities::SPLIT_METHOD_MB_STR_SPLIT, StringUtilities::$splitMethod);
        $this->assertSame(['c', 'a', 'f', "\u{00E9}"], StringUtilities::split("caf\u{00E9}"));
        $this->assertSame("f\u{00E9}", StringUtilities::substr("caf\u{00E9}", 2));

        StringUtilities::prepareSplit('cafe');

        $this->assertSame(StringUtilities::SPLIT_METHOD_STR_SPLIT, StringUtilities::$splitMethod);
        $this->assertSame(['c', 'a', 'f', 'e'], StringUtilities::split('cafe'));
        $this->assertSame('fe', StringUtilities::substr('cafe', 2));
    }
}
