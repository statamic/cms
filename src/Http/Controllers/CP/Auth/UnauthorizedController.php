<?php

namespace Statamic\Http\Controllers\CP\Auth;

class UnauthorizedController
{
    public function __invoke()
    {
        return view('statamic::auth.unauthorized');
    }
}
