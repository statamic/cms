<?php

namespace Statamic\CP;

use Statamic\API\Str;
use Statamic\FluentlyGetsAndSets;

class Column
{
    use FluentlyGetsAndSets;

    public $field;
    public $label;
    public $visible = true;

    /**
     * Make new column instance.
     *
     * @param null|string $field
     * @return static
     */
    public static function make($field = null)
    {
        $column = new static;

        return $field
            ? $column->field($field)
            : $column;
    }

    /**
     * Get or set field.
     *
     * @param null|string $field
     * @return mixed
     */
    public function field($field = null)
    {
        return $this->fluentlyGetOrSet('field', $field, function () {
            if (is_null($this->label)) {
                $this->label = Str::slugToTitle($this->field);
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
