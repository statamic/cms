<?php

namespace Statamic\CP;

use Illuminate\Support\Str;
use JsonSerializable;

class Column implements JsonSerializable
{
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
        if (is_null($handle)) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    /**
     * Get or set label.
     *
     * @param null|string $label
     * @return mixed
     */
    public function label($label = null)
    {
        if (is_null($label)) {
            return $this->label;
        }

        $this->label = __($label);

        return $this;
    }

    /**
     * Get or set visibility.
     *
     * @param null|bool $visible
     * @return mixed
     */
    public function visible($visible = null)
    {
        if (is_null($visible)) {
            return $this->visible;
        }

        $this->visible = $visible;

        return $this;
    }

    /**
     * If empty, set default label when serializing.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if (is_null($this->label)) {
            $this->label(Str::title($this->handle));
        }

        return (array) $this;
    }
}
