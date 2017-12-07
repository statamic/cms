<?php

namespace Statamic\API\Endpoint;

class Permission
{
    public function all($wildcards = false)
    {
        return app('permissions')->all($wildcards);
    }

    public function structured()
    {
        return app('permissions')->structured();
    }
}
