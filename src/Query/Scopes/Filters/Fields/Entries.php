<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Entries extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'options' => [
                    '=' => __('Is'),
                    '!=' => __('Isn\'t'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => '=',
            ],
            'value' => [
                'type' => 'entries',
                'max_items' => 1,
                'mode' => 'typeahead',
                'create' => false,
                'collections' => $this->fieldtype->config('collections'),
                'search_index' => $this->fieldtype->config('search_index'),
                'select_across_sites' => $this->fieldtype->config('select_across_sites'),
                'blueprints' => $this->fieldtype->config('blueprints'),
                'query_scopes' => $this->fieldtype->config('query_scopes'),
                'unless' => [
                    'operator' => 'contains_any null, not-null',
                ],
                'required' => false,
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];

        if (in_array($operator, ['null', 'not-null'])) {
            match ($operator) {
                'null' => $query->whereNull($handle),
                'not-null' => $query->whereNotNull($handle),
            };

            return;
        }

        if (! $id = $values['value']) {
            return;
        }

        $single = $this->fieldtype->config('max_items') === 1;

        if ($operator === '=') {
            $single
                ? $query->where($handle, $id)
                : $query->whereJsonContains($handle, [$id]);

            return;
        }

        $single
            ? $query->where($handle, '!=', $id)
            : $query->whereJsonDoesntContain($handle, [$id]);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

        if (in_array($operator, ['null', 'not-null'])) {
            return $field.' '.strtolower($translatedOperator);
        }

        if (! $id = $values['value']) {
            return null;
        }

        $title = Facades\Entry::find($id)?->value('title') ?? $id;

        return $field.' '.strtolower($translatedOperator).' '.$title;
    }
}
