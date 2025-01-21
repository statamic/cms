<?php

namespace Tests\Tags\Concerns;

use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_renders_attributes_from_array()
    {
        $this->assertEquals('', $this->tag->renderAttributes([]));

        $output = $this->tag->renderAttributes([
            'class' => 'm-0 mb-2',
            ':name' => 'first_name',
            'disabled' => 'true',
            'autocomplete' => true,
            'focusable' => false,
            'dont_render_nulls' => null,
        ]);

        $this->assertEquals('class="m-0 mb-2" :name="first_name" disabled="true" autocomplete="true" focusable="false"', $output);
    }

    #[Test]
    public function it_renders_attributes_from_params()
    {
        $this->assertEquals('', $this->tag->renderAttributesFromParams());

        $output = $this->tag
            ->setContext(['first_name' => 'Han'])
            ->setParameters([
                'class' => 'm-0 mb-2',
                ':name' => 'first_name',
                'attr:src' => 'avatar.jpg',
                'focusable' => false,
                'dont_render_nulls' => null,
                'disabled' => 'true',
                'autocomplete' => true,
            ])
            ->renderAttributesFromParams();

        $this->assertEquals('class="m-0 mb-2" name="Han" src="avatar.jpg" focusable="false" disabled="true" autocomplete="true"', $output);
    }

    #[Test]
    public function it_wont_render_attributes_for_known_params_unless_attr_prepended()
    {
        $output = $this->tag
            ->setParameters([
                'class' => 'm-0 mb-2',
                'src' => 'avatar.jpg',
                'name' => 'Han',
            ])
            ->renderAttributesFromParams(except: ['src', 'name']);

        $this->assertEquals('class="m-0 mb-2"', $output);

        $output = $this->tag
            ->setParameters([
                'class' => 'm-0 mb-2',
                'attr:src' => 'avatar.jpg',
                'name' => 'Han',
            ])
            ->renderAttributesFromParams(['src', 'name']);

        $this->assertEquals('class="m-0 mb-2" src="avatar.jpg"', $output);
    }

    #[Test]
    public function it_will_render_falsy_attributes()
    {
        $this->assertEquals('', $this->tag->renderAttributesFromParams());

        $output = $this->tag
            ->setContext(['first_name' => 'Han'])
            ->setParameters([
                'class' => 'm-0 mb-2',
                ':name' => 'first_name',
                'attr:src' => 'avatar.jpg',
                'focusable' => false,
                'dont_render_nulls' => null,
                'disabled' => 'true',
                'autocomplete' => true,
                'aria-hidden' => true,
            ])
            ->renderAttributesFromParams();

        $this->assertEquals('class="m-0 mb-2" name="Han" src="avatar.jpg" focusable="false" disabled="true" autocomplete="true" aria-hidden="true"', $output);
    }

    #[Test]
    public function it_renders_params_with_double_quotes_inside_single_quotes()
    {
        $this->assertEquals('', $this->tag->renderAttributesFromParams());

        $output = $this->tag
            ->setContext(['first_name' => 'Han'])
            ->setParameters([
                'x-data' => '{"something":"here","something_else":"there"}',
            ])
            ->renderAttributesFromParams();

        $this->assertEquals('x-data=\'{"something":"here","something_else":"there"}\'', $output);
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
