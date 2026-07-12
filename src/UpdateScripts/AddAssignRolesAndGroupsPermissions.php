<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\Role;

class AddAssignRolesAndGroupsPermissions extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.3.56');
    }

    public function update()
    {
        Role::all()->each(function ($role) {
            $requiresSave = false;
            if ($role->hasPermission('edit user groups')) {
                $role->addPermission('assign user groups');
                $requiresSave = true;
            }

            if ($role->hasPermission('edit roles')) {
                $role->addPermission('assign roles');
                $requiresSave = true;
            }

            if ($requiresSave) {
                $role->save();
            }
        });
    }
}
