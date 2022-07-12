<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Replacers\CsrfTokenReplacer;
use Statamic\StaticCaching\Replacers\NoCacheReplacer;

class Controller
{
    public function __invoke(Request $request, Session $session)
    {
        $url = $request->input('url'); // todo: maybe strip off query params?

        $session = $session->setUrl($url)->restore();

        $replacer = new NoCacheReplacer($session);

        return [
            'csrf' => [
                'token' => csrf_token(),
                'placeholder' => CsrfTokenReplacer::REPLACEMENT,
            ],
            'regions' => $session
                ->regions()
                ->map->render()
                ->map(fn ($contents) => $replacer->replace($contents)),
        ];
    }
}
