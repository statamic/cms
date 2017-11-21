<?php

namespace Statamic\Contracts\Permissions;

interface RoleFactory
{
    /**
     * @param string|array $data
     * @return \Statamic\Contracts\Permissions\Role
     */
    public function create(array $data);
}
