<?php

namespace Statamic\Exceptions;

use Statamic\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as SymfonyException;

class NotFoundHttpException extends SymfonyException
{
    public function render()
    {
        if (view()->exists('errors.404')) {
            return response($this->contents(), 404);
        }
    }

    protected function contents()
    {
        return (new View)
            ->template('errors.404')
            ->layout($this->layout())
            ->with(['response_code' => 404])
            ->render();
    }

    protected function layout()
    {
        $layouts = collect([
            'errors.layout',
            'layouts.layout',
            'layout',
            'statamic::blank',
        ]);

        return $layouts->filter()->first(function ($layout) {
            return view()->exists($layout);
        });
    }
}
