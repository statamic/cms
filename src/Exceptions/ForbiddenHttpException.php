<?php

namespace Statamic\Exceptions;

use Statamic\Facades\Cascade;
use Statamic\Statamic;
use Statamic\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForbiddenHttpException extends HttpException
{
    public function __construct(string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(403, $message, $previous, $headers, $code);
    }

    public function render()
    {
        if (Statamic::isCpRoute()) {
            return response()->view('statamic::errors.403', [], 403);
        }

        if (view()->exists('errors.403')) {
            return response($this->contents(), 403);
        }
    }

    protected function contents()
    {
        Cascade::hydrated(function ($cascade) {
            $cascade->set('response_code', 403);
        });

        return app(View::class)
            ->template('errors.403')
            ->layout($this->layout())
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
