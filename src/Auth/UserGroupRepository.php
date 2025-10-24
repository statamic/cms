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
        $blueprint = Blueprint::find('user_group') ?? Blueprint::makeFromFields([])->setHandle('user_group');

        $blueprint->ensureField('title', ['type' => 'text', 'display' => __('Title'), 'listable' => true, 'validate' => ['required'], 'instructions' => __('statamic::messages.user_groups_title_instructions')]);

        $blueprint->ensureField('roles', ['type' => 'user_roles', 'mode' => 'select', 'listable' => true, 'instructions' => __('statamic::messages.user_groups_role_instructions')]);

        UserGroupBlueprintFound::dispatch($blueprint);

        return $blueprint;
    }

    public function blueprintCommandPaletteLink()
    {
        return $this->blueprint()?->commandPaletteLink(
            type: 'Users',
            url: cp_route('blueprints.user-groups.edit'),
        );
    }
}
