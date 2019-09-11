<?php

namespace Statamic\Http\Controllers\CP\Users;

use Statamic\API\User;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Auth\User as UserContract;

class UserWizardController extends CpController
{
    public function __invoke(Request $request)
    {
        $user = User::findByEmail($request->email);

        return ['exists' => (bool) $user];
    }
}
