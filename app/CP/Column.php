<?php

namespace Statamic\CP;

use Statamic\API\Str;
use Statamic\FluentlyGetsAndSets;

class Column
{
    use FluentlyGetsAndSets;

    public $field;
    public $fieldtype;
    public $label;
    public $visible = true;
    public $value = null;

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
                $this->label(Str::slugToTitle($this->field), true);
            }
        });
    }

    /**
     * Get or set the value field.
     *
     * @param null|string $field
     * @return mixed
     */
    public function value($field = null)
    {
        return $this->fluentlyGetOrSet('value', $field);
    }

    /**
     * Get or set fieldtype.
     *
     * @param null|string $fieldtype
     * @return mixed
     */
    public function fieldtype($fieldtype = null)
    {
        return $this->fluentlyGetOrSet('fieldtype', $fieldtype);
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

    /**
     * Cast column to array.
     *
     * return array
     */
    public function toArray()
    {
        return (array) $this;
    }
}
