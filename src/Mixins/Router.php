<?php

namespace Statamic\Mixins;

use Statamic\Http\Controllers\FrontendController;
use Statamic\Support\Str;

class Router
{
    public function statamic()
    {
        return function ($uri, $view = null, $data = []) {
            if (! $view) {
                $view = Str::of($uri)->ltrim('/');
            }

            return $this->get($uri, [FrontendController::class, 'route'])
                ->defaults('view', $view)
                ->defaults('data', $data);
        };
    }
}
