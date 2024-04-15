<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

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

        $link = view('statamic::impersonator.terminate', [
            'url' => route('statamic.cp.impersonation.stop'),
        ])->render();

        return $response->setContent(str_replace('</body>', $link.'</body>', $content));
    }
}
