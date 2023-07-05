<?php

namespace Statamic\Support\Traits;

trait GetIdKey
{
    /**
     * Depending on the config, set id's will be augmented via `id` or `_id`.
     */
    protected function getIdKey(): string
    {
        if (! config('statamic.system.allow_ids_in_sets', false)) {
            return 'id';
        }

        return '_id';
    }
}
