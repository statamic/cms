<?php

namespace Statamic\Data;

use Statamic\Support\Arr;

trait TracksQueriedColumns
{
    protected $selectedQueryColumns;

    public function selectedQueryColumns($columns = null)
    {
        if (func_num_args() === 0) {
            return $this->selectedQueryColumns;
        }

        $columns = Arr::wrap($columns);

        $this->selectedQueryColumns = in_array('*', $columns) ? null : $columns;

        return $this;
    }
}
