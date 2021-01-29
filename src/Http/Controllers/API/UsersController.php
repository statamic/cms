<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;
use Statamic\Http\Resources\API\UserResource;

class UsersController extends ApiController
{
    public function index()
    {
        return app(UserResource::class)::collection(
            $this->filterSortAndPaginate(User::query())
        );
    }

    public function show($id)
    {
        return app(UserResource::class)::make($this->getUser($id));
    }

    private function getUser($id)
    {
        $user = User::find($id);

        throw_unless($user, new NotFoundHttpException("User [$id] not found."));

        return $user;
    }
}
