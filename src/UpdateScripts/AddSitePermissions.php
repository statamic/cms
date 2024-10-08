<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\Role;
use Statamic\Facades\Site;

class AddSitePermissions extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('4.27.0');
    }

    public function update()
    {
        Role::all()->each(fn ($role) => $this->updateRole($role));
    }

    private function updateRole($role)
    {
        Site::all()->each(fn ($site) => $role->addPermission("access {$site->handle()} site"));

        $role->save();
    }
}
