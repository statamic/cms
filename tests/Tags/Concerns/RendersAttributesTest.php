<?php

namespace Tests\Tags\Concerns;

use Statamic\Facades\Antlers;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags;
use Tests\TestCase;

class RendersAttributesTest extends TestCase
{
    private $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = new FakeTagWithRendersAttributes;
    }

    /** @test */
    public function it_renders_attributes_from_array()
    {
        $this->assertEquals('', $this->tag->renderAttributes([]));

        $output = $this->tag->renderAttributes([
            'attr:class' => 'm-0 mb-2',
            'attr::name' => 'first_name',
            'attr:disabled' => 'true',
            'attr:autocomplete' => true,
            'attr:focusable' => false,
            'attr:dont_render_nulls' => null,
        ]);

        $this->assertEquals('class="m-0 mb-2" :name="first_name" disabled="true" autocomplete="true" focusable="false"', $output);
    }

    /** @test */
    public function it_renders_attributes_from_params()
    {
        $this->assertEquals('', $this->tag->renderAttributesFromParams());

        $output = $this->tag
            ->setContext(['first_name' => 'Han'])
            ->setParameters([
                'attr:class' => 'm-0 mb-2',
                ':attr:name' => 'first_name',
                'attr:src' => 'avatar.jpg',
                'attr:focusable' => false,
                'attr:dont_render_nulls' => null,
                'attr:disabled' => 'true',
                'attr:autocomplete' => true,
            ])
            ->renderAttributesFromParams();

        $this->assertEquals('class="m-0 mb-2" name="Han" src="avatar.jpg" focusable="false" disabled="true" autocomplete="true"', $output);
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
