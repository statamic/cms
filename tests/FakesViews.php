<?php

namespace Tests;

use Illuminate\View\View;
use Illuminate\View\Factory;

trait FakesViews
{
    public function withFakeViews()
    {
        $this->fakeView = app(FakeViewEngine::class);
        $this->app->instance('FakeViewEngine', $this->fakeView);

        $this->app->bind('view', function ($app) {
            return new FakeViewFactory($app['view.engine.resolver'], $app['view.finder'], $app['events']);
        });
    }

    public function withStandardFakeViews()
    {
        $this->withFakeViews();

        $this->fakeView->rawContents('layout', '{{ template_content }}');
        $this->fakeView->rawContents('default', '{{ content }}');
    }

    public function withStandardFakeErrorViews()
    {
        $this->withFakeViews();

        $this->fakeView->rawContents('errors.layout', '{{ template_contents }}');
        $this->fakeView->rawContents('errors.404', 'The 404 template.');
    }
}

class FakeViewFactory extends Factory
{
    public function make($view, $data = [], $mergeData = [])
    {
        return new View($this, app('FakeViewEngine'), $view, $view, $data);
    }
}

class FakeViewEngine extends \Statamic\View\Antlers\Engine
{
    protected $rawContents = [];
    protected $renderedContents = [];

    public function rawContents($view, $contents)
    {
        $this->rawContents[$view] = $contents;
    }

    public function renderedContents($view, $contents)
    {
        $this->renderedContents[$view] = $contents;
    }

    public function get($path, array $data = [])
    {
        if (isset($this->renderedContents[$path])) {
            return $this->renderedContents[$path];
        }

        return parent::get($path, $data);
    }

    protected function getContents($path)
    {
        if (isset($this->rawContents[$path])) {
            return $this->rawContents[$path];
        }

        return parent::getContents($path);
    }
}