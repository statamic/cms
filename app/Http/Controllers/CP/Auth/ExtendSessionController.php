<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Statamic\Http\Controllers\CP\CpController;

class ExtendSessionController extends CpController
{
    public function __invoke()
    {
        return 'OK';
    }
}
