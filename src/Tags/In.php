<?php

namespace Statamic\Tags;

use Statamic\Facades\User;
use Statamic\Support\Arr;

class In extends Tags
{
    /**
     * Maps to {{ in:[group] }}.
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

        $group = $method === 'index'
            ? $this->params->explode(['group', 'groups'])
            : $method;

        $groups = Arr::wrap($group);

        foreach ($groups as $group) {
            if ($user->isInGroup($group)) {
                return $this->parse();
            }
        }
    }
}
