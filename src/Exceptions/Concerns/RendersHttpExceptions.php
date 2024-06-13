<?php

namespace Statamic\Exceptions\Concerns;

use Illuminate\Http\Response;
use Statamic\Facades\Cascade;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;
use Statamic\View\View;

trait RendersHttpExceptions
{
    public function render()
    {
        if (Statamic::isCpRoute()) {
            return response()->view('statamic::errors.'.$this->getStatusCode(), [], $this->getStatusCode());
        }

        if (Statamic::isApiRoute()) {
            return response()->json(['message' => $this->getApiMessage()], $this->getStatusCode());
        }

        if ($cached = $this->getCached404()) {
            return $cached;
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

    public function getApiMessage()
    {
        return $this->getMessage();
    }

    private function getCached404(): ?Response
    {
        if ($this->getStatusCode() !== 404) {
            return null;
        }

        $cacher = app(Cacher::class);

        $request = request();
        //        $request->setUrl('404');

        if (! $cacher->hasCachedPage($request)) {
            return null;
        }

        return $cacher->getCachedPage($request)->toResponse($request);
    }
}
