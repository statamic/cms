<?php

namespace Statamic\API;

/**
 * @deprecated since 2.1
 */
class Permissions
{
    /**
     * @deprecated since 2.1
     */
    public static function all($wildcards = false)
    {
        \Log::notice('Permissions::all() is deprecated. Use Permission::all()');

        return Permission::all($wildcards);
    }

    /**
     * @deprecated since 2.1
     */
    public static function structured()
    {
        \Log::notice('Permissions::structured() is deprecated. Use Permission::structured()');

        return Permission::structured();
    }
}
