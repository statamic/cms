<?php

namespace Statamic\StaticCaching\NoCache;

class CsrfTokenController
{
    public function __invoke()
    {
        return [
            'foo' => __('bar'),
            'csrf' => csrf_token(),
        ];
    }
}
