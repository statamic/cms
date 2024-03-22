<?php

namespace Tests\Rules;

use Statamic\Rules\Handle;
use Tests\TestCase;

class HandleTest extends TestCase
{
    use ValidatesCustomRule;

    protected static $customRule = Handle::class;

    /** @test */
    public function it_validates_handles()
    {
        $this->assertPasses('foo');
        $this->assertPasses('foo_bar');
        $this->assertPasses('foo_bar_baz');
        $this->assertPasses('foo_bar_baz_qux');

        $this->assertFails('foo-bar');
        $this->assertFails('_foo');
        $this->assertFails('foo_');
        $this->assertFails('_foo_bar');
        $this->assertFails('foo_bar_');
        $this->assertFails('foo__bar');
        $this->assertFails('foo___bar');
        $this->assertFails('1foo');
        $this->assertFails('foo2');
        $this->assertFails('foo_3bar');
        $this->assertFails('foo_4_bar');
        $this->assertFails('*foo');
        $this->assertFails('foo#');
        $this->assertFails('foo_!bar');
        $this->assertFails('foo_-_bar');
    }

    /** @test */
    public function it_outputs_helpful_validation_error()
    {
        $this->assertValidationErrorOutput('Handles must use lowercase letters with snake_case separators.', '_bad_input');
    }
}
