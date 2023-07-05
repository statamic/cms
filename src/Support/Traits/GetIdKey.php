<?php

namespace Statamic\Support\Traits;

use Statamic\Support\FluentGetterSetter;

trait GetIdKey
{
    /**
     * 
     */
    protected function getIdKey(): string
    {
        if (! config('statamic.system.allow_ids_in_sets', false)) {
            return 'id';
        }

        return '_id';
    }
}
