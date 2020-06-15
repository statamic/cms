<?php

namespace Statamic\Data;

use Illuminate\Support\Collection;

class AugmentedData extends AbstractAugmented
{
    protected $data;
    protected $array;

    public function __construct($data, $array)
    {
        $this->data = $data;
        $this->array = $array instanceof Collection ? $array->all() : $array;
    }

    public function keys()
    {
        return array_keys($this->array);
    }

    protected function getFromData($handle)
    {
        return $this->array[$handle] ?? null;
    }
}
