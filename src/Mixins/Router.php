<?php

namespace Statamic\Mixins;

use Statamic\Http\Controllers\FrontendController;

class Router
{
    public function statamic()
    {
        return function ($uri, $view, $data = []) {
            return $this->get($uri, [FrontendController::class, 'route'])
                ->defaults('view', $view)
                ->defaults('data', $data);
        };
    }
}
