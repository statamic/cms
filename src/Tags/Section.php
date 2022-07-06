<?php

namespace Statamic\Tags;

use Facades\Statamic\View\Cascade;

class Section extends Tags
{
    public function __call($method, $args)
    {
        $name = explode(':', $this->tag)[1];

        $contents = $this->parse();

        // Store the contents in both the view factory and the cascade. This way, it's retrievable in
        // a Blade template via the @yield directive, or in an Antlers template using the yield tag.
        // It would be nice to be able to just store it in the view factory, but when a Blade view
        // doesn't use an @extends, the sections get flushed and lost once the view is rendered.
        $this->storeInCascade($name, $contents);
        $this->storeInViewFactory($name, $contents);
    }

    private function storeInCascade($name, $contents)
    {
        Cascade::instance()->sections()->put($name, $contents);
    }

    private function storeInViewFactory($name, $contents)
    {
        tap(view()->shared('__env'), function ($view) use ($name, $contents) {
            $view->startSection($name);
            echo $contents;
            $view->stopSection($name);
        });
    }
}
