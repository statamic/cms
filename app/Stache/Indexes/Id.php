<?php

namespace Statamic\Stache\Indexes;

class Id extends Path
{
    public function getItems()
    {
        return collect(parent::getItems())
            ->map(function ($path, $id) {
                return $id;
            })->all();
    }
}