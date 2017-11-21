<?php

namespace Statamic\API;

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
