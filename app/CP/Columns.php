<?php

namespace Statamic\CP;

use Illuminate\Support\Collection;

class Columns extends Collection
{
    public function ensureHas($column)
    {
        if ($this->has($column->field())) {
            return $this;
        }

        return $this->put($column->field(), $column);
    }

    public function ensurePrepended($column)
    {
        if ($this->has($column->field())) {
            return $this;
        }

        return $this->prepend($column, $column->field());
    }
}
