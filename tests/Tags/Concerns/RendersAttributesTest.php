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

        $this->tag = new FakeTagWithRendersAttributes;
    }

    /** @test */
    function it_renders_attributes_from_array()
    {
        $this->assertEquals('', $this->tag->renderAttributes([]));

        $output = $this->tag->renderAttributes([
            'class' => 'm-0 mb-1',
            ':name' => 'first_name',
        ]);

        $this->assertEquals('class="m-0 mb-1" :name="first_name"', $output);
    }

    /** @test */
    function it_renders_attributes_from_params()
    {
        $this->assertEquals('', $this->tag->renderAttributesFromParams());

        $output = $this->tag
            ->setContext(['first_name' => 'Han'])
            ->setParameters([
                'class' => 'm-0 mb-1',
                ':name' => 'first_name',
                'attr:src' => 'avatar.jpg',
                'disabled' => 'true',
            ])
            ->renderAttributesFromParams();

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
            ->renderAttributesFromParams(['src', 'name']);

        $this->assertEquals('class="m-0 mb-1"', $output);

        $output = $this->tag
            ->setParameters([
                'class' => 'm-0 mb-1',
                'attr:src' => 'avatar.jpg',
                'name' => 'Han',
            ])
            ->renderAttributesFromParams(['src', 'name']);

        $this->assertEquals('class="m-0 mb-1" src="avatar.jpg"', $output);
    }
}

class FakeTagWithRendersAttributes extends Tags
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
