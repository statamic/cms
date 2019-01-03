<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\Collection;

class Collections extends Relationship
{
    protected function toItemArray($id)
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
