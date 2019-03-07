<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\Collection;

class Collections extends Relationship
{
    protected function toItemArray($id, $site = null)
    {
        if ($collection = Collection::whereHandle($id)) {
            return [
                'title' => $collection->title(),
                'id' => $collection->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }
}
