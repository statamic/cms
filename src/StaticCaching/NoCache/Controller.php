<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Replacers\NoCacheReplacer;

class Controller
{
    public function __invoke(Request $request, CacheSession $session)
    {
        $url = $request->input('url'); // todo: maybe strip off query params?

        $session = $session->setUrl($url)->restore();

        $replacer = new NoCacheReplacer($session);

        return collect($session->getSections())
            ->keys()
            ->mapWithKeys(fn ($key) => [$key => $session->getFragment($key)->render()])
            ->map(fn ($contents) => $replacer->replace($contents));
    }
}
