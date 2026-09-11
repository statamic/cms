<?php

namespace Statamic\Testing\Concerns;

use Statamic\Testing\FakeViewEngine;
use Statamic\Testing\FakeViewFactory;
use Statamic\Testing\FakeViewFinder;

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

        // Keep real namespace hints (e.g. `statamic::`) resolving so that faking
        // the frontend views doesn't break package views rendered as a side effect.
        foreach ($originalFactory->getFinder()->getHints() as $namespace => $paths) {
            $this->fakeViewFinder->addNamespace($namespace, $paths);
        }

        $this->fakeViewFactory = new FakeViewFactory($this->app['view.engine.resolver'], $this->fakeViewFinder, $this->app['events']);
        $this->fakeViewFactory->setFakeEngine($this->fakeView);
        foreach (array_reverse($originalFactory->getExtensions()) as $ext => $engine) {
            $this->fakeViewFactory->addExtension($ext, $engine);
        }

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
