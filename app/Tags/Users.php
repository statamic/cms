<?php

namespace Statamic\Tags;

use Statamic\API\User;
use Statamic\API\UserGroup;

class Users extends Tags
{

    protected $defaultAsKey = 'users';
    use GetsQueryResults, OutputsItems;

    /**
     * {{ get_content from="" }} ... {{ /get_content }}
     */
    public function index()
    {
        $query = User::query();

        if ($group = $this->get('group')) {
            $query->where('group', $group);
        }

        if ($role = $this->get('role')) {
            $query->where('role', $role);
        }

        return $this->output($this->results($query));
    }
}
