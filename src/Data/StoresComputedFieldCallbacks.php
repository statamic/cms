<?php

namespace Statamic\Data;

use Closure;
use Illuminate\Support\Collection;

trait StoresComputedFieldCallbacks
{
    protected $computedFieldCallbacks;

    /**
     * @param  string|array $field
     */
    public function computed($field, ?Closure $callback = null)
    {
        if (is_array($field)) {
            foreach ($field as $field_name => $field_callback) {
                $this->computedFieldCallbacks[$field_name] = $field_callback;
            }

            return;
        }

        $this->computedFieldCallbacks[$field] = $callback;
    }

    public function getComputedCallbacks(): Collection
    {
        return collect($this->computedFieldCallbacks);
    }
}
