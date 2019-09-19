<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;
use Statamic\Support\Arr;
use Statamic\Facades\User;

class Can extends Tags
{
    /**
     * Maps to {{ can:[permission] }}
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
