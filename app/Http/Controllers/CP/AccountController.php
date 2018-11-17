<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;

class AccountController extends CpController
{
    public function __invoke(Request $request)
    {
        return redirect($request->user()->editUrl());
    }
}
