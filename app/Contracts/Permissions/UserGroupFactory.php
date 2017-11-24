<?php

namespace Statamic\Contracts\Permissions;

interface UserGroupFactory
{
    /**
     * Create a user group
     *
     * @param array $data
     * @param string|null $uuid
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public function create($data, $uuid = null);
}
