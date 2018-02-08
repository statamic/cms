<?php

namespace Tests\View\Antlers;

use Mockery;
use Tests\TestCase;
use Statamic\View\Antlers\View;
use Statamic\Events\ViewRendered;
use Illuminate\Support\Facades\Event;
use Statamic\Extensions\View\FileViewFinder;

class ViewTest extends TestCase
{
    /** @test */
    function combines_two_views()
    {
        Event::fake();
        $finder = Mockery::mock(FileViewFinder::class);
        $finder->shouldReceive('find')->with('template')->andReturn(__DIR__.'/fixtures/template.antlers.html');
        $finder->shouldReceive('find')->with('layout')->andReturn(__DIR__.'/fixtures/layout.antlers.html');
        $this->app->make('view')->setFinder($finder);

        $view = (new View)
            ->template('template')
            ->layout('layout')
            ->data(['foo' => 'bar']);

        $this->assertEquals('Layout: bar | Template: bar', $view->render());

        Event::assertDispatched(ViewRendered::class, function ($event) use ($view) {
            return $event->view === $view;
        });
    }

    /** @test */
    function gets_data()
    {
        $view = (new View)->data(['foo' => 'bar']);

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