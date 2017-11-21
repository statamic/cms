<?php

namespace Statamic\Data\Services;

class UserGroupsService extends BaseService
{
    /**
     * The repo key
     *
     * @var string
     */
    protected $repo = 'usergroups';

    public function handle($handle)
    {
        return $this->all()->first(function ($id, $group) use ($handle) {
            return $group->slug() === $handle;
        });
    }
}