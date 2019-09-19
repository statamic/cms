<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;
use Statamic\Support\Arr;
use Statamic\Facades\User;

class Is extends Tags
{
    /**
     * Maps to {{ is:[role] }}
     *
     * @param  string $method
     * @param  array $args
     * @return string
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
