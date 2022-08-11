<?php

namespace Statamic\Exceptions;

use Statamic\Statamic;
use Statamic\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as SymfonyException;

class NotFoundHttpException extends SymfonyException
{
    public function render()
    {
        if (Statamic::isCpRoute()) {
            return response()->view('statamic::errors.404', [], 404);
        }

        if (view()->exists('errors.404')) {
            return response($this->contents(), 404);
        }
    }

    protected function contents()
    {
        return app(View::class)
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
