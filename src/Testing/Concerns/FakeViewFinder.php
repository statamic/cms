<?php

namespace Statamic\Testing\Concerns;

use Illuminate\View\FileViewFinder;

class FakeViewFinder extends FileViewFinder
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
