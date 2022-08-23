<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\Role;
use Statamic\Statamic;

class AddGraphQLPermission extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.3.32');
    }

    public function update()
    {
        if (config('statamic.graphql.enabled', false) && Statamic::pro()) {
            Role::all()->each(function ($role) {
                $this->updateRole($role);
            });
        }
    }

    private function updateRole($role)
    {
        $role->addPermission('view graphql');
        $role->save();
    }
}
