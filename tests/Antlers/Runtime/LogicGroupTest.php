<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class LogicGroupTest extends ParserTestCase
{
    public function test_gatekeeper_right_side_is_lazy()
    {
        $template = '{{ thing ?= (stuff = "abc") }}--{{ stuff }}';

        $result = $this->renderString($template, [
            'thing' => false,
        ]);

        $this->assertSame('--', $result);
    }
}
