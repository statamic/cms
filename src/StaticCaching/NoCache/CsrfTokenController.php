<?php

namespace Statamic\StaticCaching\NoCache;

class CsrfTokenController
{
    public function __invoke()
    {
        return csrf_token();
    }
}
