<?php

namespace Statamic\CP;

use Illuminate\Support\Collection;
use Statamic\Facades\Preference;

class Columns extends Collection
{
    /**
     * Ensure has column.
     *
     * @param  \Statamic\CP\Column  $column
     * @return $this
     */
    public function ensureHas($column)
    {
        if ($this->keyBy->field()->has($column->field())) {
            return $this;
        }

        return $this->put($column->field(), $column);
    }

    /**
     * Ensure has column, and if not prepend.
     *
     * @param  \Statamic\CP\Column  $column
     * @return $this
     */
    public function ensurePrepended($column)
    {
        if ($this->keyBy->field()->has($column->field())) {
            return $this;
        }

        return $this->prepend($column, $column->field());
    }

    /**
     * Reject unlisted columns.
     *
     * @return $this
     */
    public function rejectUnlisted()
    {
        return $this->filter->listable();
    }

    /**
     * Set preferred column visibility and order.
     *
     * @param  mixed  $preferred
     * @return Columns
     */
    public function setPreferred($preferred)
    {
        if (is_string($preferred)) {
            $preferred = Preference::get($preferred);
        }

        if (! $preferred) {
            return $this;
        }

        $this->items = $this
            ->values()
            ->keyBy(function ($column, $key) use ($preferred) {
                $preferredKey = array_search($column->field(), $preferred ?? []);

                return $preferredKey !== false ? $preferredKey : $key + 10000;
            })
            ->sortKeys()
            ->map(function ($column) use ($preferred) {
                return $column->visible(in_array($column->field(), $preferred));
            })
            ->values()
            ->all();

        return $this;
    }
}
