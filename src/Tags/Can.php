<?php

namespace Statamic\Tags;

use Statamic\Facades\User;
use Statamic\Support\Arr;

class Can extends Tags
{
    /**
     * Maps to {{ can:[permission] }}.
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

        $permission = $method === 'index'
            ? $this->params->explode(['permission', 'do'])
            : $method;

        $permissions = Arr::wrap($permission);

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $this->parse();
            }
        }
    }
}
