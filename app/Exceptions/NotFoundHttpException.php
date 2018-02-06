<?php

namespace Statamic\Exceptions;

use Facades\Statamic\Cascade;
use Statamic\View\Antlers\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as SymfonyException;

class NotFoundHttpException extends SymfonyException
{
    public function render()
    {
        return response($this->contents(), 404);
    }

    protected function contents()
    {
        return (new View)
            ->template('errors.404')
            ->layout($this->layout())
            ->data(Cascade::instance()->hydrate()->toArray())
            ->render();
    }

    protected function layout()
    {
        $layouts = collect([
            'errors.layout',
            config('statamic.theming.views.layout'),
            'statamic::blank'
        ]);

        return $layouts->filter()->first(function ($layout) {
            return view()->exists($layout);
        });
    }
}