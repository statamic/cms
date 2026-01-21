<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades;
use Statamic\Support\Arr;

class Terms extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'options' => [
                    'like' => __('Contains'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => 'like',
            ],
            'term' => [
                'type' => 'terms',
                'placeholder' => __('Term'),
                'clearable' => true,
                'mode' => 'select',
                'max_items' => 1,
                'taxonomies' => $this->fieldtype->taxonomies(),
                'if' => [
                    'operator' => 'contains_any like',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];

        match ($operator) {
            'like' => $this->fieldtype->config('max_items') === 1
                ? $query->where($handle, 'like', "%{$values['term']}%")
                : $query->whereJsonContains($handle, $values['term']),
            'null' => $query->whereNull($handle),
            'not-null' => $query->whereNotNull($handle),
        };
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];

        if (in_array($operator, ['null', 'not-null'])) {
            $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

            return $field.' '.strtolower($translatedOperator);
        }

        $id = $this->fieldtype->usingSingleTaxonomy()
            ? $this->fieldtype->taxonomies()[0].'::'.$values['term']
            : $values['term'];

        $term = Facades\Term::find($id)->title();

        return $field.': '.$term;
    }

    public function isComplete($values): bool
    {
        $values = array_filter($values);

        return Arr::has($values, 'operator') && Arr::has($values, 'term');
    }
}
