<?php

namespace Tests;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\View\Factory;
use Illuminate\View\View;

trait FakesViews
{
    protected $fakeView;
    protected $fakeViewFinder;
    protected $fakeViewFactory;

    public function withFakeViews()
    {
        $originalFactory = $this->app['view'];

        $this->fakeView = app(FakeViewEngine::class);
        $this->fakeViewFinder = new FakeViewFinder($this->app['files'], config('view.paths'));

        $this->fakeViewFactory = new FakeViewFactory($this->app['view.engine.resolver'], $this->fakeViewFinder, $this->app['events']);
        foreach (array_reverse($originalFactory->getExtensions()) as $ext => $engine) {
            $this->fakeViewFactory->addExtension($ext, $engine);
        }

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
        $this->fakeViewFinder->fakeViews[$view] = $view;
        $this->fakeViewFactory->fileExtensions[$view] = $extension;
    }

    public function viewShouldReturnRendered($view, $contents, $extension = 'antlers.html')
    {
        $this->fakeView->renderedContents["$view.$extension"] = $contents;
        $this->fakeViewFinder->fakeViews[$view] = $view;
        $this->fakeViewFactory->fileExtensions[$view] = $extension;
    }
}

class FakeViewFactory extends Factory
{
    public $fileExtensions = [];

    public function make($view, $data = [], $mergeData = [])
    {
        $engine = app('FakeViewEngine');
        $ext = $this->fileExtensions[$view] ?? 'antlers.html';

        if ($engine->exists($view)) {
            return new View($this, $engine, $view, "{$view}.{$ext}", $data);
        }

        return parent::make($view, $data, $mergeData);
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

        if (Str::endsWith($path, '.blade.php')) {
            return Blade::render($this->rawContents[$path], $data);
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
    public $fakeViews = [];

    public function find($view)
    {
        if (isset($this->fakeViews[$view])) {
            return $this->fakeViews[$view];
        }

        return parent::find($view);
    }

    public function exists($path)
    {
        return isset($this->fakeViews[$path]);
    }
}
