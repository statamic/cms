<?php

namespace Statamic\Exceptions\Concerns;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Statamic\Facades\Cascade;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Statamic\View\View;

trait RendersHttpExceptions
{
    private static ?Closure $renderCallback = null;

    public function render(Request $request)
    {
        if (static::$renderCallback && ($response = Closure::fromCallable(static::$renderCallback)->call($this, $request))) {
            return $response;
        }

        if (Statamic::isCpRoute()) {
            return response()->view('statamic::errors.'.$this->getStatusCode(), [], $this->getStatusCode());
        }

        if (Statamic::isApiRoute()) {
            return response()->json(['message' => $this->getApiMessage()], $this->getStatusCode());
        }

        if ($cached = $this->getCachedError()) {
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
            config('statamic.system.layout', 'layout'),
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

    private function getCachedError(): ?Response
    {
        $status = $this->getStatusCode();

        if (! config('statamic.static_caching.share_errors')) {
            return null;
        }

        $cacher = app(Cacher::class);

        if (! $cacher instanceof ApplicationCacher) {
            return null;
        }

        $request = Request::createFrom(request())->fakeStaticCacheStatus($status);

        return $cacher->hasCachedPage($request)
            ? $cacher->getCachedPage($request)->toResponse($request)
            : null;
    }

    public static function renderUsing(Closure $callback): void
    {
        static::$renderCallback = $callback;
    }
}
