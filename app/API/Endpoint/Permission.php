<?php

namespace Statamic\API\Endpoint;

class Permission
{
    public static function all($wildcards = false)
    {
        return app('permissions')->all($wildcards);
    }

    public static function structured()
    {
        return app('permissions')->structured();
    }
}
