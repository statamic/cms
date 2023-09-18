<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\Role;

class AddConfigureFormFieldsPermission extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.4.8');
    }

    public function update()
    {
        Role::all()->each(function ($role) {
            if ($role->hasPermission('configure fields')) {
                $role->addPermission('configure form fields');
                $role->save();
            }
        });
    }
}
