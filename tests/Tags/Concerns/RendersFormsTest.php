<?php

namespace Tests\Tags\Concerns;

use Statamic\Facades\Antlers;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags;
use Tests\TestCase;

class RendersFormsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->tag = new FakeTagWithRendersForms;
    }

    /** @test */
    public function it_renders_form_open_tags()
    {
        $output = $this->tag->formOpen('http://localhost:8000/submit');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost:8000/submit">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
        $this->assertStringNotContainsString('<input type="hidden" name="_method"', $output);
    }

    /** @test */
    public function it_renders_form_open_tags_with_custom_method()
    {
        $output = $this->tag->formOpen('http://localhost:8000/submit', 'DELETE');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost:8000/submit">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="DELETE">', $output);
    }

    /** @test */
    public function it_renders_form_open_tags_with_custom_attributes()
    {
        $output = $this->tag
            ->setParameters([
                'class' => 'mb-1',
                'attr:id' => 'form',
                'method' => 'this should not render',
                'action' => 'this should not render',
            ])
            ->formOpen('http://localhost:8000/submit', 'DELETE');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost:8000/submit" class="mb-1" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="DELETE">', $output);
    }

    /** @test */
    public function it_renders_form_close_tag()
    {
        $this->assertEquals('</form>', $this->tag->formClose());
    }
}

class FakeTagWithRendersForms extends Tags
{
    use Concerns\RendersForms;

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
