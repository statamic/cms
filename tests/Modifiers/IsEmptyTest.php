<?php

namespace Tests\Modifiers;

use Statamic\Facades\Antlers;
use Statamic\Support\Str;
use Tests\TestCase;

class IsEmptyTest extends TestCase
{
    /** @test */
    function it_checks_if_its_empty()
    {
        $this->assertTrue($this->parse('')); // empty string is empty
        $this->assertTrue($this->parse([])); // empty array is empty

        $this->assertFalse($this->parse(['foo' => 'bar'])); // definitely not empty

        $this->assertTrue($this->parse(['foo' => ''])); // just consists of empty strings
        $this->assertTrue($this->parse(['foo' => '', 'bar' => '']));

        $this->assertFalse($this->parse(null)); // nulls are not empty
        $this->assertFalse($this->parse(['foo' => null])); // array of nulls are not empty
        $this->assertFalse($this->parse(['foo' => '', 'bar' => null]));

        $this->assertTrue($this->parse(['foo' => []])); // recursion
        $this->assertTrue($this->parse(['foo' => ['bar' => []]]));
        $this->assertTrue($this->parse(['foo' => ['bar' => ['baz' => '']]]));
        $this->assertFalse($this->parse(['foo' => ['bar' => ['baz' => 'qux']]]));
        $this->assertFalse($this->parse(['foo' => ['bar' => ['baz' => null]]]));
    }

    function parse($arr)
    {
        return Str::toBool(Antlers::parse('{{ if arr|is_empty }}true{{ else }}false{{ /if }}', ['arr' => $arr]));
    }
}
