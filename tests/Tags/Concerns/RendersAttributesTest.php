<?php

namespace Tests\Tags\Concerns;

use Statamic\Facades\Antlers;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags;
use Tests\TestCase;

class RendersAttributesTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        $this->tag = new FakeTag;
    }

    /** @test */
    function it_renders_empty_string_by_default()
    {
        $this->assertEquals('', $this->tag->renderAttributes());
    }

    /** @test */
    function it_renders_attributes()
    {
        $output = $this->tag
            ->setContext(['first_name' => 'Han'])
            ->setParameters([
                'class' => 'm-0 mb-1',
                ':name' => 'first_name',
                'attr:src' => 'avatar.jpg',
                'disabled' => 'true',
            ])
            ->renderAttributes();

        $this->assertEquals('class="m-0 mb-1" name="Han" src="avatar.jpg" disabled', $output);
    }

    /** @test */
    function it_wont_render_attributes_for_known_params_unless_attr_prepended()
    {
        $output = $this->tag
            ->setParameters([
                'class' => 'm-0 mb-1',
                'src' => 'avatar.jpg',
                'name' => 'Han',
            ])
            ->renderAttributes(['src', 'name']);

        $this->assertEquals('class="m-0 mb-1"', $output);

        $output = $this->tag
            ->setParameters([
                'class' => 'm-0 mb-1',
                'attr:src' => 'avatar.jpg',
                'name' => 'Han',
            ])
            ->renderAttributes(['src', 'name']);

        $this->assertEquals('class="m-0 mb-1" src="avatar.jpg"', $output);
    }
}

class FakeTag extends Tags
{
    use Concerns\RendersAttributes;

    public function __construct()
    {
        $this
            ->setParser(Antlers::parser())
            ->setContext([])
            ->setParameters([]);
    }

    public function __call($method, $arguments)
    {
        return $this->{$method}(...$arguments);
    }
}
