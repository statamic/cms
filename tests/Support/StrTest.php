<?php

namespace Tests;

use Tests\TestCase;
use Statamic\Support\Str;

class StrTest extends TestCase
{
    /** @test */
    function undefined_methods_get_passed_to_stringy()
    {
        $this->assertFalse(method_exists(Str::class, 'last'));
        $this->assertEquals('bar', Str::last('foobar', 3));
    }

    /** @test */
    function it_converts_to_boolean_strings()
    {
        $this->assertEquals('true', Str::bool(true));
        $this->assertEquals('false', Str::bool(false));
    }

    /** @test */
    function it_converts_to_booleans()
    {
        $this->assertTrue(Str::toBool('true'));
        $this->assertTrue(Str::toBool('yes'));
        $this->assertTrue(Str::toBool('really anything'));

        $this->assertFalse(Str::toBool('false'));
        $this->assertFalse(Str::toBool('no'));
        $this->assertFalse(Str::toBool('0'));
        $this->assertFalse(Str::toBool(''));
        $this->assertFalse(Str::toBool('-1'));
    }
}
