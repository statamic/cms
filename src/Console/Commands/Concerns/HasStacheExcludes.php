<?php

namespace Statamic\Console\Commands\Concerns;

use Statamic\Facades\Stache;

trait HasStacheExcludes
{
    protected function addExcludes(?string $excludes = null): void
    {
        if (empty($excludes)) {
            return;
        }

        foreach (explode(',', $excludes) as $key) {
            Stache::exclude(trim($key));
        }
    }
}
