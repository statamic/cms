<?php

namespace Tests\Rules;

use Statamic\Rules\Slug;
use Tests\TestCase;

class SlugTest extends TestCase
{
    use ValidatesCustomRule;

    protected static $customRule = Slug::class;

    /** @test */
    public function it_validates_handles()
    {
        $this->assertPasses('foo');
        $this->assertPasses('foo-bar');
        $this->assertPasses('foo-bar-baz');
        $this->assertPasses('foo-bar-baz-qux');

        $this->assertFails('foo_bar');
        $this->assertFails('-foo');
        $this->assertFails('foo-');
        $this->assertFails('-foo-bar');
        $this->assertFails('foo-bar-');
        $this->assertFails('foo--bar');
        $this->assertFails('foo---bar');
        $this->assertFails('1foo');
        $this->assertFails('foo2');
        $this->assertFails('foo-3bar');
        $this->assertFails('foo-4-bar');
        $this->assertFails('*foo');
        $this->assertFails('foo#');
        $this->assertFails('foo-!bar');
        $this->assertFails('foo-_-bar');
    }

    /** @test */
    public function it_outputs_helpful_validation_error()
    {
        $this->assertValidationErrorOutput('Slugs must use lowercase letters with slug-case separators.', '-bad-input');
    }
}
