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

    public function hasQueriedColumn(string $column)
    {
        if ($this->selectedQueryColumns === null || in_array('*', $this->selectedQueryColumns)) {
            return true;
        }

        return in_array($column, $this->selectedQueryColumns ?? []);
    }
}
