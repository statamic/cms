<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\Role;

class AddPerEntryPermissions extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.1');
    }

    public function update()
    {
        Role::all()->each(function ($role) {
            $this->updateRole($role);
        });
    }

    private function updateRole($role)
    {
        $this->getMatchingPermissions($role->permissions(), '/^edit (\w+) entries$/')
            ->filter
            ->capture
            ->each(function ($match) use ($role) {
                $role->addPermission("edit other authors {$match->capture} entries");
            });

        $this->getMatchingPermissions($role->permissions(), '/^publish (\w+) entries$/')
            ->filter
            ->capture
            ->each(function ($match) use ($role) {
                $role->addPermission("publish other authors {$match->capture} entries");
            });

        $this->getMatchingPermissions($role->permissions(), '/^delete (\w+) entries$/')
            ->filter
            ->capture
            ->each(function ($match) use ($role) {
                $role->addPermission("delete other authors {$match->capture} entries");
            });

        $role->save();
    }

    private function getMatchingPermissions($permissions, $regex)
    {
        return $permissions
            ->map(function ($permission) use ($regex) {
                $found = preg_match($regex, $permission, $matches);

                return $found
                    ? (object) ['permission' => $permission, 'capture' => $matches[1] ?? null]
                    : null;
            })
            ->filter();
    }
}
