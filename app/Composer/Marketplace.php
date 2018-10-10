<?php

namespace Statamic\Composer;

use Facades\App\Services\Composer;

class Marketplace
{
    /**
     * Get marketplace approved addons.
     */
    public function approvedAddons()
    {
        // Will actually get approved packages from marketplace database later.
        return collect([
            'laravel/dusk',
            'laravel/scout',
        ]);
    }
}
