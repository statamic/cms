<?php

namespace Statamic\Testing;

use Illuminate\View\Factory;
use Illuminate\View\View;

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
