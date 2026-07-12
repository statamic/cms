<?php

namespace Statamic\Tags;

use Statamic\Facades\User;
use Statamic\Support\Arr;

class Is extends Tags
{
    /**
     * Maps to {{ is:[role] }}.
     *
     * @param  string  $method
     * @param  array  $args
     * @return string|void
     */
    public function wildcard($method)
    {
        if (! $user = User::current()) {
            return;
        }

        $role = $method === 'index'
            ? $this->params->explode(['role', 'roles'])
            : $method;

        $roles = Arr::wrap($role);

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $this->parse();
            }
        }
    }
}
