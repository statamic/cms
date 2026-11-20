<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\Role;

class AddViewOtherAuthorsEntriesPermissions extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.59');
    }

    public function update()
    {
        Role::all()->each(fn ($role) => $this->updateRole($role));
    }

    private function updateRole($role)
    {
        $this->getMatchingPermissions($role->permissions(), '/^edit other authors (\w+) entries$/')
            ->filter
            ->capture
            ->each(function ($match) use ($role) {
                $role->addPermission("view other authors {$match->capture} entries");
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
