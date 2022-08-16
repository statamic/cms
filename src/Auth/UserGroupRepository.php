<?php

namespace Statamic\Auth;

use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Contracts\Auth\UserGroupRepository as RepositoryContract;
use Statamic\Events\UserGroupBlueprintFound;
use Statamic\Facades\Blueprint;

abstract class UserGroupRepository implements RepositoryContract
{
    public function find($id): ?UserGroupContract
    {
        return $this->all()->get($id);
    }

    public function blueprint()
    {
        $blueprint = Blueprint::find('user_group') ?? Blueprint::makeFromFields([
            'title' => ['type' => 'text', 'display' => __('Title'), 'listable' => true, 'instructions' => __('Usually a plural noun, like Editors or Photographers')],
            'handle' => ['type' => 'slug', 'display' => __('Handle'), 'listable' => true, 'instructions' => __('Used to reference this user group on the frontend. It\'s non-trivial to change later.')],
            'roles' => ['type' => 'user_roles', 'mode' => 'select', 'listable' => true, 'instructions' => __('Assign roles to give users in this group all of their corresponding permissions.')],
        ])->setHandle('user_group');

        UserGroupBlueprintFound::dispatch($blueprint);

        return $blueprint;
    }
}
