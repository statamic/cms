<?php

namespace Tests;

use Illuminate\View\Factory;
use Illuminate\View\View;
use InvalidArgumentException;

trait FakesViews
{
    public function withFakeViews()
    {
        $this->fakeView = app(FakeViewEngine::class);
        $this->fakeViewFinder = new FakeViewFinder($this->app['files'], config('view.paths'));
        $this->fakeViewFactory = new FakeViewFactory($this->app['view.engine.resolver'], $this->app['view.finder'], $this->app['events']);
        $this->app->instance('FakeViewEngine', $this->fakeView);
        $this->app->instance('view.finder', $this->fakeViewFinder);
        $this->app->instance('view', $this->fakeViewFactory);
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

        $this->viewShouldReturnRaw('errors.layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('errors.404', 'The 404 template.');
    }

    public function viewShouldReturnRaw($view, $contents, $extension = 'antlers.html')
    {
        $this->fakeView->rawContents["$view.$extension"] = $contents;
        $this->fakeViewFinder->views[$view] = $view;
        $this->fakeViewFactory->extensions[$view] = $extension;
    }

    public function viewShouldReturnRendered($view, $contents, $extension = 'antlers.html')
    {
        $this->fakeView->renderedContents["$view.$extension"] = $contents;
        $this->fakeViewFinder->views[$view] = $view;
        $this->fakeViewFactory->extensions[$view] = $extension;
    }
}

class FakeViewFactory extends Factory
{
    public $extensions = [];

    public function make($view, $data = [], $mergeData = [])
    {
        $engine = app('FakeViewEngine');
        $ext = $this->extensions[$view] ?? 'antlers.html';

        if (! $engine->exists($view)) {
            throw new InvalidArgumentException("View [{$view}] not found.");
        }

        return new View($this, $engine, $view, "{$view}.{$ext}", $data);
    }

    public function exists($view)
    {
        return app('FakeViewEngine')->exists($view);
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

    public function exists($path)
    {
        return app('view.finder')->exists($path);
    }
}

class FakeViewFinder extends \Illuminate\View\FileViewFinder
{
    public $views = [];

    public function find($view)
    {
        if (isset($this->views[$view])) {
            return $this->views[$view];
        }

        return parent::find($view);
    }

    public function exists($path)
    {
        return isset($this->views[$path]);
    }
}
