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
                'type' => 'select',
                'options' => $this->options()->all(),
                'placeholder' => __('Term'),
                'clearable' => true,
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
                ? $query->where($handle, $values['term'])
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

    protected function options()
    {
        return collect($this->fieldtype->taxonomies())
            ->map(function ($handle) {
                return Facades\Taxonomy::find($handle);
            })
            ->filter()
            ->flatMap(function ($taxonomy) {
                return $taxonomy->queryTerms()->get();
            })
            ->mapWithKeys(function ($term) {
                $value = $this->fieldtype->usingSingleTaxonomy() ? $term->slug() : $term->id();

                return [$value => $term->title()];
            });
    }
}
