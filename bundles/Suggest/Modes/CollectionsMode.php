<?php

namespace Statamic\Addons\Suggest\Modes;

use Statamic\API\Collection;

class CollectionsMode extends AbstractMode
{
    public function suggestions()
    {
        $suggestions = [];

        $collections = Collection::all();

        foreach ($collections as $handle => $collection) {
            $suggestions[] = [
                'value' => $handle,
                'text'  => $this->label($collection, 'title')
            ];
        }

        return $suggestions;
    }
}
