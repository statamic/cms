<?php

namespace Tests\View\Antlers;

use Mockery;
use Tests\TestCase;
use Tests\FakesViews;
use Statamic\View\View;
use Illuminate\Support\Facades\Event;
use Statamic\View\Events\ViewRendered;
use Statamic\Extensions\View\FileViewFinder;

class ViewTest extends TestCase
{
    use FakesViews;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFakeViews();
    }

    /** @test */
    function combines_two_views()
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
    function template_is_rendered_alone_if_no_layout_is_provided()
    {
        $this->viewShouldReturnRaw('template', file_get_contents(__DIR__.'/fixtures/template.antlers.html'));

        $view = (new View)
            ->template('template')
            ->with(['foo' => 'bar']);

        $this->assertEquals('Template: bar', $view->render());
    }

    /** @test */
    function a_non_antlers_template_will_not_attempt_to_load_the_layout()
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
    function template_with_noparse_is_left_unparsed()
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
    function layout_with_noparse_is_left_unparsed()
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
    function layout_and_template_with_noparse_is_left_unparsed()
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
    function gets_data()
    {
        $view = (new View)->with(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $view->data());
    }

    /** @test */
    function gets_template()
    {
        $view = (new View)->template('foo');

        $this->assertEquals('foo', $view->template());
    }

    /** @test */
    function gets_layout()
    {
        $view = (new View)->layout('foo');

        $this->assertEquals('foo', $view->layout());
    }
}
