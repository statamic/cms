<?php

namespace Statamic\Tags\Dictionary;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Statamic\Data\ContainsSupplementalData;

class DictionaryItem implements Arrayable
{
    use ContainsSupplementalData;

    public function __construct(public array $data)
    {
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->toArray(), $key, $default);
    }

    public function toArray()
    {
        return array_merge($this->data, $this->supplements ?? []);
    }
}
