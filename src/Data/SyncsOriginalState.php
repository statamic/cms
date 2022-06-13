<?php

namespace Statamic\Data;

use Statamic\Support\Arr;

trait SyncsOriginalState
{
    protected $original = [];

    public function syncOriginal()
    {
        $this->original = [];

        if ($this->syncOriginalProperties) {
            foreach ($this->syncOriginalProperties as $property) {
                $this->original[$property] = $this->{$property};
            }
        }

        return $this;
    }

    public function getOriginal($key = null, $fallback = null)
    {
        return Arr::get($this->original, $key, $fallback);
    }
}
