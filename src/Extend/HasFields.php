<?php

namespace Statamic\Extend;

use Statamic\Fields\Fields;

trait HasFields
{
    abstract function fieldItems();

    public function fields()
    {
        $fields = collect($this->fieldItems())->map(function ($field, $handle) {
            return compact('handle', 'field');
        });

        return new Fields($fields);
    }
}
