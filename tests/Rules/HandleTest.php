<?php

namespace Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Rules\Handle;
use Tests\TestCase;

class HandleTest extends TestCase
{
    use ValidatesCustomRule;

    protected static $customRule = Handle::class;

    #[Test]
    public function it_validates_handles()
    {
        $this->assertPasses('foo');
        $this->assertPasses('foo_bar');
        $this->assertPasses('foo_bar_baz');
        $this->assertPasses('foo_bar_baz_qux');
        $this->assertPasses('foo1');
        $this->assertPasses('foo123');
        $this->assertPasses('foo123_20bar');
        $this->assertPasses('FooBar');

        $this->assertFails('foo-bar');
        $this->assertFails('_foo');
        $this->assertFails('foo_');
        $this->assertFails('_foo_bar');
        $this->assertFails('foo_bar_');
        $this->assertFails('foo__bar');
        $this->assertFails('foo___bar');
        $this->assertFails('1foo');
        $this->assertFails('*foo');
        $this->assertFails('foo#');
        $this->assertFails('foo_!bar');
        $this->assertFails('foo_-_bar');
    }

    #[Test]
    public function it_outputs_helpful_validation_error()
    {
        $this->assertValidationErrorOutput(trans('statamic::validation.handle'), '_bad_input');
    }

    #[Test]
    public function it_outputs_helpful_validation_error_when_string_starts_with_number()
    {
        $this->assertValidationErrorOutput(trans('statamic::validation.handle_starts_with_number'), '1bad_input');
    }
}
