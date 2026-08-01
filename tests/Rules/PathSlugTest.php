<?php

namespace Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Rules\PathSlug;
use Tests\TestCase;

class PathSlugTest extends TestCase
{
    use ValidatesCustomRule;

    protected static $customRule = PathSlug::class;

    #[Test]
    public function it_validates_path_slugs()
    {
        $this->assertPasses('foo');
        $this->assertPasses('foo-bar');
        $this->assertPasses('_index');
        $this->assertPasses('guide/routing');
        $this->assertPasses('guide/_index');
        $this->assertPasses('a/b/c-d');

        $this->assertFails('-foo');
        $this->assertFails('_foo-bar');
        $this->assertFails('/guide');
        $this->assertFails('guide/');
        $this->assertFails('guide//routing');
        $this->assertFails('guide/_foo');
    }

    #[Test]
    public function it_outputs_helpful_validation_error()
    {
        $this->assertValidationErrorOutput(trans('statamic::validation.path_slug'), '-bad-input');
    }
}
