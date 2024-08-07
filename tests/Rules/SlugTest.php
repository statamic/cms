<?php

namespace Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Rules\Slug;
use Tests\TestCase;

class SlugTest extends TestCase
{
    use ValidatesCustomRule;

    protected static $customRule = Slug::class;

    #[Test]
    public function it_validates_slugs()
    {
        $this->assertPasses('foo');
        $this->assertPasses('foo-bar');
        $this->assertPasses('foo-bar-baz');
        $this->assertPasses('foo_bar_baz');
        $this->assertPasses('foo-bar-baz-qux');
        $this->assertPasses('1-foo-bar234-baz-qux-5');
        $this->assertPasses('1_foo_bar234_baz_qux_5');
        $this->assertPasses('1-foo_bar234-baz_qux-5');

        $this->assertFails('-foo');
        $this->assertFails('foo-');
        $this->assertFails('-foo-bar');
        $this->assertFails('_foo-bar');
        $this->assertFails('foo-bar-');
        $this->assertFails('foo-bar_');
        $this->assertFails('foo--bar');
        $this->assertFails('foo__bar');
        $this->assertFails('foo---bar');
        $this->assertFails('foo___bar');
        $this->assertFails('*foo');
        $this->assertFails('foo#');
        $this->assertFails('foo-!bar');
        $this->assertFails('foo_!bar');
        $this->assertFails('foo-_-bar');
    }

    #[Test]
    public function it_outputs_helpful_validation_error()
    {
        $this->assertValidationErrorOutput(trans('statamic::validation.slug'), '-bad-input');
    }
}
