<?php

namespace Tests\View\Antlers;

use Mockery;
use Tests\TestCase;
use Statamic\View\Antlers\View;
use Statamic\Extensions\View\FileViewFinder;

class ViewTest extends TestCase
{
    /** @test */
    function combines_two_views()
    {
        $finder = Mockery::mock(FileViewFinder::class);
        $finder->shouldReceive('find')->with('template')->andReturn(__DIR__.'/fixtures/template.antlers.html');
        $finder->shouldReceive('find')->with('layout')->andReturn(__DIR__.'/fixtures/layout.antlers.html');
        $this->app->make('view')->setFinder($finder);

        $rendered = (new View)
            ->template('template')
            ->layout('layout')
            ->data(['foo' => 'bar'])
            ->render();

        $this->assertEquals('Layout: bar | Template: bar', $rendered);
    }
}