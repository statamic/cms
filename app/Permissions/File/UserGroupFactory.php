<?php

namespace Statamic\Permissions\File;

use Statamic\API\Helper;
use Statamic\Contracts\Permissions\UserGroupFactory as UserGroupFactoryContract;

class UserGroupFactory implements UserGroupFactoryContract
{
    protected $data;
    protected $id;

    /**
     * Create a user group
     *
     * @param array       $data
     * @param string|null $uuid
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public function create($data, $uuid = null)
    {
        $this->data = $data;

        $group = $this->group();

        $this->id = $uuid ?: Helper::makeUuid();
        $group->uuid($this->id);

        $group->title(array_get($this->data, 'title'));
        $group->slug(array_get($this->data, 'slug'));

        foreach ($this->getUsers() as $user) {
            $group->addUser($user);
        }

        foreach (array_get($this->data, 'roles', []) as $role) {
            $group->addRole($role);
        }

        return $group;
    }

    protected function getUsers()
    {
        return array_get($this->data, 'users', []);
    }

    /**
     * @return \Statamic\Contracts\Permissions\UserGroup|\Statamic\Permissions\File\UserGroup
     */
    private function group()
    {
        return app('Statamic\Contracts\Permissions\UserGroup');
    }
}
