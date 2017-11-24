<?php

namespace Statamic\Addons\Relate;

use Statamic\Addons\Suggest\SuggestFieldtype;

class RelateFieldtype extends SuggestFieldtype
{
    public function preProcess($data)
    {
        $max_items = (int) $this->getFieldConfig('max_items');

        $data = (array) $data;

        if ($max_items > 1) {
            return array_slice($data, 0, $max_items);
        }

        // When being used as a config fieldtype and the max items is one, we want to only
        // save the value. This is different to the regular behavior of having an array
        // even if there is only one item. An example of this is how the pages
        // fieldtype uses another pages fieldtype for its "parent" value.
        if ($this->is_config && $max_items == 1) {
            return empty($data) ? null : $data[0];
        }

        return $data;
    }

    public function process($data)
    {
        $max_items = (int) $this->getFieldConfig('max_items');

        if ($max_items === 1 && is_array($data)) {
            return $data[0];
        }

        return $data;
    }
}
