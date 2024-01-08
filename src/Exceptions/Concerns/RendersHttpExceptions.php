<?php

namespace Statamic\Exceptions\Concerns;

use Statamic\Facades\Cascade;
use Statamic\Statamic;
use Statamic\View\View;

trait RendersHttpExceptions
{
    public function render()
    {
        if (Statamic::isCpRoute()) {
            return response()->view('statamic::errors.'.$this->getStatusCode(), [], $this->getStatusCode());
        }

        if (view()->exists('errors.'.$this->getStatusCode())) {
            return response($this->contents(), $this->getStatusCode());
        }
    }

    protected function contents()
    {
        Cascade::hydrated(function ($cascade) {
            $cascade->set('response_code', $this->getStatusCode());
        });

        return app(View::class)
            ->template('errors.'.$this->getStatusCode())
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
