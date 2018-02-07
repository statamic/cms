<?php

namespace Tests;

use Illuminate\View\View;
use Illuminate\View\Factory;

trait FakesViews
{
    public function withFakeViews()
    {
        $this->fakeView = app(FakeViewEngine::class);
        $this->fakeViewFinder = new FakeViewFinder($this->app['files'], config('view.paths'));
        $this->app->instance('FakeViewEngine', $this->fakeView);
        $this->app->instance('view.finder', $this->fakeViewFinder);

        $this->app->bind('view', function ($app) {
            return new FakeViewFactory($app['view.engine.resolver'], $app['view.finder'], $app['events']);
        });
    }

    public function withStandardFakeViews()
    {
        $this->withFakeViews();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', '{{ content }}');
    }

    public function withStandardFakeErrorViews()
    {
        $this->withFakeViews();

        $this->viewShouldReturnRaw('errors.layout', '{{ template_contents }}');
        $this->viewShouldReturnRaw('errors.404', 'The 404 template.');
    }

    public function viewShouldReturnRaw($view, $contents)
    {
        $this->fakeView->rawContents[$view] = $contents;
        $this->fakeViewFinder->views[$view] = $view;
    }

    public function viewShouldReturnRendered($view, $contents)
    {
        $this->fakeView->renderedContents[$view] = $contents;
        $this->fakeViewFinder->views[$view] = $view;
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
    public $rawContents = [];
    public $renderedContents = [];

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

class FakeViewFinder extends \Statamic\Extensions\View\FileViewFinder
{
    public $views = [];

    public function find($view)
    {
        if (isset($this->views[$view])) {
            return $this->views[$view];
        }

        return parent::find($view);
    }
}