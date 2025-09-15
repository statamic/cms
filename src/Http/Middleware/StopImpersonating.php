<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Statamic\Facades\Preference;

class StopImpersonating
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (! $this->shouldInjectLink($response)) {
            return $response;
        }

        return $this->injectLink($response);
    }

    private function shouldInjectLink($response)
    {
        if (! session()->get('statamic_impersonated_by')) {
            return false;
        }

        if (! $response instanceof Response) {
            return false;
        }

        if (! $content = $response->content()) {
            return false;
        }

        if (stripos($content, '<html') === false) {
            return false;
        }

        return true;
    }

    private function injectLink($response)
    {
        $content = $response->content();

        $locale = Preference::get('locale') ?? app()->getLocale();

        // Make locale config with dashes backwards compatible, as they should be underscores.
        $locale = str_replace('-', '_', $locale);

        $link = view('statamic::impersonator.terminate', [
            'url' => route('statamic.cp.impersonation.stop'),
            'locale' => $locale,
        ])->render();

        return $response->setContent(str_replace('</body>', $link.'</body>', $content));
    }
}
