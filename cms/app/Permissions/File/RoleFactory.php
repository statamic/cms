<?php

namespace Statamic\Permissions\File;

use Statamic\API\Helper;
use Statamic\Contracts\Permissions\RoleFactory as RoleFactoryContract;

class RoleFactory implements RoleFactoryContract
{
    /**
     * @param string|array $data
     * @param string|null  $uuid
     * @return \Statamic\Contracts\Permissions\Role
     */
    public function create(array $data, $uuid = null)
    {
        $role = $this->role();

        $uuid = $uuid ?: Helper::makeUuid();
        $role->uuid($uuid);

        $role->title(array_get($data, 'title'));
        $role->slug(array_get($data, 'slug'));

        foreach (array_get($data, 'permissions', []) as $permission) {
            $role->addPermission($permission);
        }

        return $role;
    }

    /**
     * @return \Statamic\Contracts\Permissions\Role
     */
    private function role()
    {
        return app('Statamic\Contracts\Permissions\Role');
    }
}
