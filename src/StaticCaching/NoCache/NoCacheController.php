<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Statamic\StaticCaching\Replacers\NoCacheReplacer;
use Statamic\Support\Str;

class NoCacheController
{
    public function __invoke(Request $request, Session $session)
    {
        $request->validate(['url' => 'required|string']);

        $url = $request->input('url');

        if (Str::endsWith($url, '?')) {
            $url = Str::replaceLast('?', '', $url);
        }

        if (Str::contains($url, '?')) {
            $url = Str::before($url, '?').'?'.Request::normalizeQueryString(Str::after($url, '?'));
        }

        $session->setPaginationUrl($url);

        if (config('statamic.static_caching.ignore_query_strings', false)) {
            $url = explode('?', $url)[0];
        }

        $session = $session->setUrl($url)->restore();

        $replacer = new NoCacheReplacer($session);

        return [
            'csrf' => csrf_token(),
            'regions' => $session
                ->regionKeys()
                ->mapWithKeys(fn (string $key) => [$key => $this->render($session, $replacer, $key)])
                ->reject(fn ($contents) => is_null($contents)),
        ];
    }

    private function render(Session $session, NoCacheReplacer $replacer, string $key): ?string
    {
        try {
            return $replacer->replace($session->region($key)->render());
        } catch (RegionNotFound $e) {
            Log::debug("Static cache region [{$e->getRegion()}] not found while rendering region [{$key}]. Skipping it.");

            return null;
        }
    }
}
