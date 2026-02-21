<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Arr;
use Statamic\Support\Str;

/**
 * Integer filter but with a "seconds" appended to the input for clarity
 */
class Duration extends Integer
{
    public function fieldItems()
    {
        $fields = parent::fieldItems();
        $append = trim(__('statamic::messages.units.s', ['count' => '']));

        return [
            ...$fields,
            'value' => [...$fields['value'], 'append' => $append],
        ];
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = Str::durationForHumans($values['value']);

        return $field.' '.strtolower($translatedOperator).' '.$value;
    }
}
