<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Arr;
use Statamic\Support\Str;

/**
 * Special integer field that takes input in KB, queries in bytes, and displays a human-readable badge.
 */
class FileSize extends Integer
{
    public function fieldItems()
    {
        $fields = parent::fieldItems();
        $append = trim(__('statamic::messages.units.KB', ['count' => '']));

        return [
            ...$fields,
            'value' => [...$fields['value'], 'append' => $append],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $query->where('size', $values['operator'], $values['value'] * 1024);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = Str::fileSizeForHumans($values['value'] * 1024, 0);

        return $field.' '.strtolower($translatedOperator).' '.$value;
    }
}
