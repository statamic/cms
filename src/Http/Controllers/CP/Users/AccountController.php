<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Auth\User;
use Statamic\Http\Controllers\CP\CpController;

class AccountController extends CpController
{
    public function __invoke(Request $request)
    {
        return redirect(User::fromUser($request->user())->editUrl());
    }
}
