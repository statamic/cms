<?php

namespace Statamic\Policies;

class StructurePolicy
{
    public function view($user, $structure)
    {
        return $user->hasPermission(
            "view {$structure->handle()} structure"
        );
    }
}
