<?php

namespace Statamic;

use Illuminate\Foundation\Application as Laravel;

class Application extends Laravel
{
    /**
     * Crank it up to eleven!
     *
     * @return void
     */
    public function toEleven()
    {
        if (! $memoryLimit = config('statamic.system.php_max_memory_limit')) {
            $memoryLimit = -1;
        }

        // Moar memory!
        @ini_set('memory_limit', $memoryLimit);

        // Moar time!
        @set_time_limit(0);
    }
}
