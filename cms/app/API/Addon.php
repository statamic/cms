<?php

namespace Statamic\API;

use Statamic\Extend\Addon as AddonInstance;
use Statamic\Extend\Management\AddonManager;

class Addon
{
    /**
     * @return AddonManager
     */
    public static function manager()
    {
        return app(AddonManager::class);
    }

    /**
     * Create an addon instance.
     *
     * @param string $name  The name of the addon. This will be converted to StudlyCase.
     * @return AddonInstance
     */
    public static function create($name)
    {
        return new AddonInstance($name);
    }
}
