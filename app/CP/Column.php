<?php

namespace Statamic\CP;

use Illuminate\Support\Str;
use Statamic\FluentlyGetsAndSets;

class Column
{
    use FluentlyGetsAndSets;

    public $handle;
    public $label;
    public $visible = true;

    /**
     * Make new column instance.
     *
     * @param null|string $handle
     * @return static
     */
    public static function make($handle = null)
    {
        $column = new static;

        return $handle
            ? $column->handle($handle)
            : $column;
    }

    /**
     * Get or set handle.
     *
     * @param null|string $handle
     * @return mixed
     */
    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle', $handle, function () {
            if (is_null($this->label)) {
                $this->label(Str::title($this->handle));
            }
        });
    }

    /**
     * Get or set label.
     *
     * @param null|string $label
     * @return mixed
     */
    public function label($label = null)
    {
        return $this->fluentlyGetOrSet('label', $label);
    }

    /**
     * Get or set visibility.
     *
     * @param null|bool $visible
     * @return mixed
     */
    public function visible($visible = null)
    {
        return $this->fluentlyGetOrSet('visible', $visible);
    }
}
