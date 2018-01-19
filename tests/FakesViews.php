<?php

namespace Tests;

use Mockery;
use Illuminate\View\View;
use Illuminate\View\Factory;
use Illuminate\Contracts\View\Engine;

trait FakesViews
{
    protected $mockViewEngine;

    protected function withFakeViews()
    {
        $this->mockViewEngine = Mockery::mock(Engine::class);
        $this->app->instance('engine-fake', $this->mockViewEngine);

        $this->app->bind('view', function ($app) {
            return new FakeViewFactory($app['view.engine.resolver'], $app['view.finder'], $app['events']);
        });

        return $this;
    }
}

class FakeViewFactory extends Factory
{
    public function make($view, $data = [], $mergeData = [])
    {
        return new View($this, app('engine-fake'), $view, $view, $data);
    }
}