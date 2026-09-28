<?php

namespace Statamic\Testing;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Statamic\View\Antlers\Engine;

class FakeViewEngine extends Engine
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
