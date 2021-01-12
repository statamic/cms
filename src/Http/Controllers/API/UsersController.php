<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;
use Statamic\Http\Resources\API\UserResource;

class UsersController extends ApiController
{
    public function index(Request $request)
    {
        return app(UserResource::class)::collection(
            $this->filterSortAndPaginate(User::query())
        );
    }

    public function show($user)
    {
        throw_unless(
            $user = User::find($user),
            new NotFoundHttpException()
        );

        return app(UserResource::class)::make($user);
    }
}
