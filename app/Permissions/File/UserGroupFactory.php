<?php

namespace Statamic\Permissions\File;

use Statamic\API\Helper;
use Statamic\Contracts\Permissions\UserGroupFactory as UserGroupFactoryContract;

class UserGroupFactory implements UserGroupFactoryContract
{
    /**
     * Create a user group
     *
     * @param array       $data
     * @param string|null $uuid
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public function create($data, $uuid = null)
    {
        $group = $this->group();

        $uuid = $uuid ?: Helper::makeUuid();
        $group->uuid($uuid);

        $group->title(array_get($data, 'title'));
        $group->slug(array_get($data, 'slug'));

        foreach (array_get($data, 'users', []) as $user) {
            $group->addUser($user);
        }

        foreach (array_get($data, 'roles', []) as $role) {
            $group->addRole($role);
        }

        return $group;
    }

    /**
     * @return \Statamic\Contracts\Permissions\UserGroup|\Statamic\Permissions\File\UserGroup
     */
    private function group()
    {
        return app('Statamic\Contracts\Permissions\UserGroup');
    }
}
