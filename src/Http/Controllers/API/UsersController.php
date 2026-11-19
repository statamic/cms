<?php

namespace Statamic\Http\Controllers\API;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\QueryScopeAuthorizer;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;
use Statamic\Http\Resources\API\UserResource;

class UsersController extends ApiController
{
    protected $resourceConfigKey = 'users';

    public function index()
    {
        $this->abortIfDisabled();

        return app(UserResource::class)::collection(
            $this->updateAndPaginate(User::query())
        );
    }

    public function show($id)
    {
        $this->abortIfDisabled();

        return app(UserResource::class)::make($this->getUser($id));
    }

    private function getUser($id)
    {
        $user = User::find($id);

        throw_unless($user, new NotFoundHttpException("User [$id] not found."));

        return $user;
    }

    protected function allowedFilters()
    {
        return collect(FilterAuthorizer::allowedForResource('api', 'users'))
            ->reject(fn ($field) => in_array($field, ['password', 'password_hash']))
            ->all();
    }

    protected function allowedQueryScopes()
    {
        return QueryScopeAuthorizer::allowedForResource('api', 'users');
    }
}
