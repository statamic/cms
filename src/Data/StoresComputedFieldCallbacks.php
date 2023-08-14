<?php

namespace Statamic\Data;

use Closure;
use Illuminate\Support\Collection;

trait StoresComputedFieldCallbacks
{
    protected $computedFieldCallbacks;

    public function computed(string $field, Closure $callback)
    {
        $this->computedFieldCallbacks[$field] = $callback;
    }

    public function getComputedCallbacks(): Collection
    {
        return collect($this->computedFieldCallbacks);
    }
}
