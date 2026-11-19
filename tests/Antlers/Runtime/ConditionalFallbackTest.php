<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class ConditionalFallbackTest extends ParserTestCase
{
    public function test_it_processes_conditional_fallback()
    {
        $expectedText = 'font-medium text-gray-900';

        $this->assertSame(null, $this->evaluateRaw("is_current || is_parent ?= 'font-medium text-gray-900'", []));
        $this->assertSame(null, $this->evaluateRaw("is_current || is_parent ?= 'font-medium text-gray-900'", [
            'is_current' => false,
            'is_parent' => false,
        ]));
        $this->assertSame(null, $this->evaluateRaw("is_current || is_parent ?= 'font-medium text-gray-900'", [
            'is_current' => null,
            'is_parent' => null,
        ]));

        $this->assertSame($expectedText, $this->evaluateRaw("is_current || is_parent ?= 'font-medium text-gray-900'", [
            'is_current' => true,
            'is_parent' => true,
        ]));
        $this->assertSame($expectedText, $this->evaluateRaw("is_current || is_parent ?= 'font-medium text-gray-900'", [
            'is_current' => false,
            'is_parent' => true,
        ]));
        $this->assertSame($expectedText, $this->evaluateRaw("is_current || is_parent ?= 'font-medium text-gray-900'", [
            'is_current' => true,
            'is_parent' => false,
        ]));
        $this->assertSame('text-green-800', $this->evaluateRaw("type == 'success' ?= 'text-green-800'", [
            'type' => 'success',
        ]));
    }
}
