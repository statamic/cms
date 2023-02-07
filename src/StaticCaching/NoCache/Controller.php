<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Replacers\NoCacheReplacer;
use Statamic\Support\Str;

class Controller
{
    public function __invoke(Request $request, Session $session)
    {
        $url = $request->input('url'); // todo: maybe strip off query params?

        if (Str::contains($url, '?')) {
            $url = Str::before($url, '?').'?'.Request::normalizeQueryString(Str::after($url, '?'));
        }

        $session = $session->setUrl($url)->restore();

        $replacer = new NoCacheReplacer($session);

        return [
            'csrf' => csrf_token(),
            'regions' => $session
                ->regions()
                ->map->render()
                ->map(fn ($contents) => $replacer->replace($contents)),
        ];
    }
}
