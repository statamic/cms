<?php

namespace Tests\Antlers\Runtime;

use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;

class StrictVariablesTest extends ParserTestCase
{
    public function test_strict_array_variable_access_returns_count_from_modifier()
    {
        $vars = [
            'test' => ['one', 'two', 'three'],
        ];

        $this->assertSame(3, intval($this->renderString('{{ $test | count }}', $vars)));
    }

    public function test_strict_array_variable_access_can_be_iterated()
    {
        $vars = [
            'test' => ['one', 'two', 'three'],
        ];

        $this->assertSame('onetwothree', $this->renderString('{{ $$test }}{{ value }}{{ /$$test }}', $vars));
    }

    public function test_array_strict_variable_does_not_conflict_with_tag_that_has_same_name()
    {
        $vars = [
            'test' => ['one', 'two', 'three'],
        ];

        (new class extends Tags
        {
            public static $handle = 'test';

            public function index()
            {
                return ['four', 'five', 'six'];
            }
        })::register();

        $this->assertSame('onetwothree', $this->renderString('{{ $$test }}{{ value }}{{ /$$test }}', $vars));
        $this->assertSame('onetwothree', $this->renderString('{{ test }}{{ value }}{{ /test }}', $vars));
        $this->assertSame('fourfivesix', $this->renderString('{{ %test }}{{ value }}{{ /%test }}', $vars, true));
    }
}
