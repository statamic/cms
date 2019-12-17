<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\API\UserResource;

class UsersController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $users = static::paginate(User::all()->values());

        return app(UserResource::class)::collection($users);
    }
}
