<?php

namespace Statamic\Testing;

use Illuminate\View\Factory;
use Illuminate\View\View;

class FakeViewFactory extends Factory
{
    public $fileExtensions = [];

    protected $fakeEngine;

    public function setFakeEngine(FakeViewEngine $engine)
    {
        $this->fakeEngine = $engine;

        return $this;
    }

    public function make($view, $data = [], $mergeData = [])
    {
        $ext = $this->fileExtensions[$view] ?? 'antlers.html';

        if ($this->fakeEngine->exists($view)) {
            return new View($this, $this->fakeEngine, $view, "{$view}.{$ext}", $data);
        }

        return parent::make($view, $data, $mergeData);
    }

    public function exists($view)
    {
        return $this->fakeEngine->exists($view);
    }
}
