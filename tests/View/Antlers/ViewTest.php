<?php

namespace Tests\View\Antlers;

use Illuminate\Support\Facades\Event;
use Statamic\View\Events\ViewRendered;
use Statamic\View\View;
use Tests\FakesViews;
use Tests\TestCase;

class ViewTest extends TestCase
{
    use FakesViews;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFakeViews();
    }

    /** @test */
    public function combines_two_views()
    {
        Event::fake();
        $this->viewShouldReturnRaw('template', file_get_contents(__DIR__.'/fixtures/template.antlers.html'));
        $this->viewShouldReturnRaw('layout', file_get_contents(__DIR__.'/fixtures/layout.antlers.html'));

        $view = (new View)
            ->template('template')
            ->layout('layout')
            ->with(['foo' => 'bar']);

        $this->assertEquals('Layout: bar | Template: bar', $view->render());

        Event::assertDispatched(ViewRendered::class, function ($event) use ($view) {
            return $event->view === $view;
        });
    }

    /** @test */
    public function a_layout_can_be_in_the_layouts_directory()
    {
        $this->viewShouldReturnRaw('template', file_get_contents(__DIR__.'/fixtures/template.antlers.html'));
        $this->viewShouldReturnRaw('layouts.test', file_get_contents(__DIR__.'/fixtures/layout.antlers.html'));

        $view = (new View)
            ->template('template')
            ->layout('test')
            ->with(['foo' => 'bar']);

        $this->assertEquals('Layout: bar | Template: bar', $view->render());
    }

    /** @test */
    public function template_is_rendered_alone_if_no_layout_is_provided()
    {
        $this->viewShouldReturnRaw('template', file_get_contents(__DIR__.'/fixtures/template.antlers.html'));

        $view = (new View)
            ->template('template')
            ->with(['foo' => 'bar']);

        $this->assertEquals('Template: bar', $view->render());
    }

    /** @test */
    public function a_non_antlers_template_will_not_attempt_to_load_the_layout()
    {
        Event::fake();
        $this->viewShouldReturnRaw('template', file_get_contents(__DIR__.'/fixtures/template.antlers.html'), 'blade.php');
        $this->viewShouldReturnRaw('layout', file_get_contents(__DIR__.'/fixtures/layout.antlers.html'));

        $view = (new View)
            ->template('template')
            ->layout('layout')
            ->with(['foo' => 'bar']);

        $this->assertEquals('Template: bar', $view->render());

        Event::assertDispatched(ViewRendered::class, function ($event) use ($view) {
            return $event->view === $view;
        });
    }

    /** @test */
    public function template_with_noparse_is_left_unparsed()
    {
        $this->viewShouldReturnRaw('partial-with-noparse', file_get_contents(__DIR__.'/fixtures/partial-with-noparse.antlers.html'));
        $this->viewShouldReturnRaw('template', file_get_contents(__DIR__.'/fixtures/template-with-noparse.antlers.html'));
        $this->viewShouldReturnRaw('layout', file_get_contents(__DIR__.'/fixtures/layout.antlers.html'));

        $view = (new View)
            ->template('template')
            ->layout('layout')
            ->with(['foo' => 'bar']);

        $this->assertEquals('Layout: bar | Template: {{ foo }} | Partial: {{ foo }}', $view->render());
    }

    /** @test */
    public function layout_with_noparse_is_left_unparsed()
    {
        $this->viewShouldReturnRaw('template', file_get_contents(__DIR__.'/fixtures/template.antlers.html'));
        $this->viewShouldReturnRaw('layout', file_get_contents(__DIR__.'/fixtures/layout-with-noparse.antlers.html'));

        $view = (new View)
            ->template('template')
            ->layout('layout')
            ->with(['foo' => 'bar']);

        $this->assertEquals('Layout: {{ foo }} | Template: bar', $view->render());
    }

    /** @test */
    public function layout_and_template_with_noparse_is_left_unparsed()
    {
        $this->viewShouldReturnRaw('partial-with-noparse', file_get_contents(__DIR__.'/fixtures/partial-with-noparse.antlers.html'));
        $this->viewShouldReturnRaw('template', file_get_contents(__DIR__.'/fixtures/template-with-noparse.antlers.html'));
        $this->viewShouldReturnRaw('layout', file_get_contents(__DIR__.'/fixtures/layout-with-noparse.antlers.html'));

        $view = (new View)
            ->template('template')
            ->layout('layout')
            ->with(['foo' => 'bar']);

        $this->assertEquals('Layout: {{ foo }} | Template: {{ foo }} | Partial: {{ foo }}', $view->render());
    }

    /** @test */
    public function gets_data()
    {
        $view = (new View)->with(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $view->data());
    }

    /** @test */
    public function gets_template()
    {
        $view = (new View)->template('foo');

        $this->assertEquals('foo', $view->template());
    }

    /** @test */
    public function gets_layout()
    {
        $view = (new View)->layout('foo');

        $this->assertEquals('foo', $view->layout());
    }

    /** @test */
    public function view_data_can_be_accessed_from_template_and_layout()
    {
        $this->viewShouldReturnRaw('template', file_get_contents(__DIR__.'/fixtures/template-with-front-matter.antlers.html'));
        $this->viewShouldReturnRaw('layout', file_get_contents(__DIR__.'/fixtures/layout-with-front-matter.antlers.html'));

        $view = (new View)
            ->template('template')
            ->layout('layout');

        $expected = <<<'EOT'
layout:
layout-foo
template-bar

template:
template-foo
template-bar
EOT;

        $this->assertEquals($expected, trim($view->render()));
    }
}
