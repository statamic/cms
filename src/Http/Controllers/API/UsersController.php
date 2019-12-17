<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\User;
use Illuminate\Http\Request;
use Statamic\Http\Resources\UserResource;
use Statamic\Http\Controllers\CP\CpController;

class UsersController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $users = static::paginate(User::all()->values());

        return app(UserResource::class)::collection($users);
    }
}
