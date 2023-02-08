<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Replacers\NoCacheReplacer;

class Controller
{
    public function __invoke(Request $request, Session $session)
    {
        $url = $request->input('url');

        if (config('statamic.static_caching.ignore_query_strings', false)) {
            $url = explode('?', $url)[0];
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
