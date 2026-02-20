<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Arr;
use Statamic\Support\Str;

class Filesize extends Integer
{
    public function fieldItems()
    {
        $fields = parent::fieldItems();

        return [
            ...$fields,
            'value' => [
                ...$fields['value'],
                'append' => trim(__('statamic::messages.units.KB', ['count' => ''])),
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $query->where($handle, $values['operator'], $values['value'] * 1024);
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
