<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class UserWizardController extends CpController
{
    public function __invoke(Request $request)
    {
        $user = User::findByEmail($request->email);

        return ['exists' => (bool) $user];
    }
}
