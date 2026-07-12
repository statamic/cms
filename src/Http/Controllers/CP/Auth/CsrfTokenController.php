<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Statamic\Http\Controllers\CP\CpController;

class CsrfTokenController extends CpController
{
    public function __invoke()
    {
        return csrf_token();
    }
}
